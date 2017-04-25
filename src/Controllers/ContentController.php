<?php
 
namespace GroupON\Controllers;
 
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Templates\Twig;
use Plenty\Modules\Order\Models\Order;


use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Account\Address\Contracts\AddressRepositoryContract;
use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Account\Contact\Contracts\ContactAddressRepositoryContract;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;
use Plenty\Modules\EventProcedures\Events\EventProceduresTriggered;

use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;
use GroupON\Models\Groupon;
use Plenty\Plugin\Log\Loggable;

class ContentController extends Controller
{
    use Loggable;
    private $orderRepository;
    private $addressRepository;
    private $configRepository;
    private $countryRepositoryContract;
    private $contactRepositoryContract;
    private $contactAddressRepositoryContract;
    private $variationSkuRepositoryContract;
    private $authHelper;
    
    public function __construct(
        OrderRepositoryContract $orderRepository,
        AddressRepositoryContract $addressRepository,
        ConfigRepository $configRepository,
        CountryRepositoryContract $countryRepositoryContract,
        VariationSkuRepositoryContract $variationSkuRepositoryContract,
        ContactRepositoryContract $contactRepositoryContract,
        ContactAddressRepositoryContract $contactAddressRepositoryContract,
        AuthHelper $authHelper
    )
    {
        $this->orderRepository = $orderRepository;
        $this->addressRepository = $addressRepository;
        $this->configRepository = $configRepository;
        $this->countryRepositoryContract = $countryRepositoryContract;
        $this->variationSkuRepositoryContract = $variationSkuRepositoryContract;
        $this->contactRepositoryContract = $contactRepositoryContract;
        $this->contactAddressRepositoryContract = $contactAddressRepositoryContract;
        $this->authHelper = $authHelper;
    }
    
    public function test(Twig $twig):string
    {
        $groupOnOrders = $this->getGroupOnOrders();
        foreach($groupOnOrders as $groupOnOrder)
        {
            
            $exists = $this->checkIfExists($groupOnOrder->orderid);
            if ($exists == false) 
            {   
                $this->getLogger(__FUNCTION__)->info("Nera", json_encode($exists)); 
                $order = $this->generateOrder($groupOnOrder);
            }
            else
            {
                $this->getLogger(__FUNCTION__)->info("Yra", json_encode($exists)); 
                $order = 'Nesukurti';
            }
        }
        $templateData = array("supplierID" => json_encode($order));
        return $twig->render('GroupON::content.test',$templateData);
    }    
    
    public function markAsExported($groupOnOrder)
    {   
        $supplierID = $this->configRepository->get('GroupON.supplierID');
        $token = $this->configRepository->get('GroupON.token');
        $lineItemsIds = [];
        foreach($groupOnOrder->line_items as $item)
        {
            $lineItemsIds[] = $item->ci_lineitemid;
        }
        
        $datatopost = array (
            "supplier_id" => $supplierID,
            "token" => $token,
            "ci_lineitem_ids" => json_encode ($lineItemsIds),
        );
        
        
        $ch = curl_init ("https://scm.commerceinterface.com/api/v2/mark_exported");
        curl_setopt ($ch, CURLOPT_POST, true);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $datatopost);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec ($ch);
        if($response) 
        {
          $response_json = json_decode( $response );
          if( $response_json->success == true ) {
            //Successfully marked as exported (only items which are not already marked exported
          } else {
            
          }
        }
       
       return $response;
    }


    public function getGroupOnOrders()
    {
        $supplierID = $this->configRepository->get('GroupON.supplierID');
        $token = $this->configRepository->get('GroupON.token');
        $url = 'https://scm.commerceinterface.com/api/v2/get_orders?supplier_id='.$supplierID.'&token='.$token.'&start_datetime=04/11/2017+00:01&end_datetime=04/11/2017+23:59';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch); 
        curl_close($ch);      
        $groupOnData = json_decode($response);
        /*$this->getLogger(__FUNCTION__)->info('Orders From GroupON',"Order from $url  response: $response"); */
        return $groupOnData->data;
    }
    
    public function generateOrderItemLists($groupOnItems)
    {
        $amounts = [];
        $orderItems = [];
        foreach($groupOnItems as $groupOnItem)
        {
            $findVariationID = $this->variationSkuRepositoryContract->search(array("sku" => $groupOnItem->sku));
            if (!is_null($findVariationID[0]->variationId)){
                $amounts[] = [
                'currency' => 'EU',
                'priceOriginalGross' => $groupOnItem->unit_price,
                'priceOriginalNet' => $groupOnItem->unit_price
                ];
                     
                $orderItems[] = [
                    'typeId' => 11,
                    'quantity' => $groupOnItem->quantity,
                    'orderItemName' => $groupOnItem->name,
                    'itemVariationId' => $findVariationID[0]->variationId,
                    'referrerId' => 10,
                    'countryVatId' => 1,
                    'amounts' => $amounts,
                    'properties' => 
                    [
                        [
                            'typeId' => 17,
                            'value' => (string)$groupOnItem->ci_lineitemid
                        ],
                        [
                            'typeId' => 18,
                            'value' => (string)$groupOnItem->voucher_code
                        ],
                    ]
                ];    
            }
            else
            {
                return null;    
            }
        }
        return $orderItems;
    }
    

    public function createDeliveryAddress($groupOnOrder,$customer)
    {
        $countryISO = $groupOnOrder->customer->country;
        
        if(empty($countryISO))
        {
          $countryISO  = "DE";
        }
        
        $formatAddress = $this->checkAddress($groupOnOrder->customer->address1);

        if(is_array($formatAddress) && isset($formatAddress) )
        {
            $street = $formatAddress['Street'];
            $houseNumber = $formatAddress['HouseNumber'];
        }
        else
        {
            $street = $groupOnOrder->customer->address1;
            $houseNumber = $groupOnOrder->customer->address1;
        }
        
        
        $parts = explode(" ", $groupOnOrder->customer->name);
        $lastname = array_pop($parts);
        $firstname = implode(" ", $parts);


        $country = $this->countryRepositoryContract->getCountryByIso($countryISO,"isoCode2");
        $deliveryAddress = $this->addressRepository->createAddress([
            'name2' => $firstname,
            'name3' => $lastname,
            'address1' => $street,
            'address2' => $houseNumber,
            'street' => $street,
            'houseNumber'=>$houseNumber,
            'town' => $groupOnOrder->customer->city,
            'postalCode' => $groupOnOrder->customer->zip,
            'countryId' => $country->id,
            'phone' => $groupOnOrder->customer->phone
        ]);
        
        if(isset($deliveryAddress->id) && isset($customer->id))
        {
            $addContactAddress = $this->contactAddressRepositoryContract->addAddress($deliveryAddress->id,$customer->id,2);
            /*$this->getLogger(__FUNCTION__)->info('Address Connected with Customer',"info : $addContactAddress"); */
            return $deliveryAddress;
        }
        else
        {
            /*$this->getLogger(__FUNCTION__)->error('Address not connected with Customer',json_encode($customer)); */
            return null;
        }
        
    }
    
    public function createCustomer($groupOnOrder)
    {
        
        $parts = explode(" ", $groupOnOrder->customer->name);
        $lastname = array_pop($parts);
        $firstname = implode(" ", $parts);
        
        $data = 
        [
            'typeId'=>1,
            'firstName' => $firstname,
            'lastName' => $lastname,
            'formOfAddress' => 0,
            'lang'=>'de',
            'referrerId' => 1,
            'plentyId' => 0,
            'privatePhone' => $groupOnOrder->customer->phone,
        ];

      
        $customer = $this->contactRepositoryContract->createContact($data); 
        if(isset($customer->id))
        {
            /*$this->getLogger(__FUNCTION__)->info('Customer Created Successfully',"Customer : $customer"); */
            return $customer;        
        }
        else
        {
           /* $this->getLogger(__FUNCTION__)->error('Customer not created',"Customer : $customer"); */
            return null;
        }
    }
    
    
    public function Procedure(EventProceduresTriggered $eventTriggered)
    {
        $order = $eventTriggered->getOrder();
        $datatopost = $this->formateFeedBack($order);
        if(!empty($datatopost))
        {
            $ch = curl_init ("https://scm.commerceinterface.com/api/v2/tracking_notification");
            curl_setopt ($ch, CURLOPT_POST, true);
            curl_setopt ($ch, CURLOPT_POSTFIELDS, $datatopost);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec ($ch);
            if($response) 
            {
              $response_json = json_decode( $response );
              if( $response_json->success == true ) 
              {
                /*$this->getLogger(__FUNCTION__)->info('Succesfull response From GroupON',"FeedBack was sended\n.$response"); */
              } 
              else 
              {
               /* $this->getLogger(__FUNCTION__)->error('Bad Response From GroupON',"Something was wrong\n.$response"); */
              }
            }        
        }
    }
    
    
    public function formateFeedBack($order)
    {
        
        $supplierID = $this->configRepository->get('GroupON.supplierID');
        $token = $this->configRepository->get('GroupON.token');
        $carrier = $this->configRepository->get('GroupON.carrier');
        $lineItemIds = [];
        $packageNumber = $this->orderRepository->getPackageNumbers($order->id);
        foreach($order->orderItems as $orderItems)
        {
            foreach($orderItems->properties as $properties)
            {
                if((int)$properties->typeId == 17)
                {
                    $lineItemIds[] = 
                    [
                        "ci_lineitem_id" => $properties->value,
                        "carrier" => $carrier,
                        "tracking" => $packageNumber[0],
                        "quantity" => $orderItems->quantity
                    ];
                }
            }
        }
        
        $datatopost = array (
            "supplier_id" => $supplierID,
            "token" => $token,
            "tracking_info" => json_encode ($lineItemIds)
            );
        
        
        return $datatopost;
    
    }
    
    
    
    public function checkAddress($address)
    {   
        $removeNumber =  preg_match_all('/\d+/', $address, $matches);
        
        if ($removeNumber > 0 OR empty($address) == true)
        {
            $houseNumber = $matches[0][0];
            $street = str_replace($houseNumber,'', $address);
        }
        else 
        {
            $houseNumber = $address;
            $street = $address;
        }
        
        $address = 
        [
            'HouseNumber' => $houseNumber,
            'Street' => $street
        ];
        
      /*  $this->getLogger(__FUNCTION__)->info('House Number',json_encode($address)); */
        return $address;
    }
    
    public function saveOrder($orderData)
    {
        $database = pluginApp(DataBase::class);
 
        $order = pluginApp(Groupon::class);
 
        $order->orderID = $orderData->id;
        foreach($orderData->properties as $properties)
        {
            if($properties->typeId == 7)
            {
                $order->externalOrderID = $properties->value;
            }
        }
        $database->save($order);
        return $order; 
    }
    
    public function checkIfExists($orderID)
    {
        $exist = $this->authHelper->processUnguarded(
        function () use ($orderID) 
        {
            $contract = pluginApp(OrderRepositoryContract::class);
            $setFilter = $contract->setFilters(['externalOrderId' => (string)$orderID ]);
            $orderList = $contract->searchOrders();
            $test = $orderList->page;
            $this->getLogger(__FUNCTION__)->info('OrderLists',json_encode($orderList)); 
            $this->getLogger(__FUNCTION__)->info('totalsCount',json_encode($test)); 
            if($orderList->totalsCount != 0)
            {
                return true;
            }
            else
            {
                return false;
            }
        });
        return $exist;
    }
    
    public function generateOrder($groupOnOrder)
    {
        $order = $this->authHelper->processUnguarded(
        function () use ($groupOnOrder) 
        {
            $customer = $this->createCustomer($groupOnOrder);
            $deliveryAddress = $this->createDeliveryAddress($groupOnOrder,$customer);
            if(!is_null($customer) && !is_null($deliveryAddress))
            {
                $orderItems = $this->generateOrderItemLists($groupOnOrder->line_items);
                if (!is_null($orderItems)) 
                {
                    $addOrder = $this->orderRepository->createOrder(
                    [
                        'typeId' => 1,
                        'methodOfPaymentId' => 4040,
                        'shippingProfileId' => 6,
                        'statusId' => 5.0, 
                        'ownerId' => 107,
                        'plentyId' => 0,
                        'orderItems' => $orderItems,
                        'properties' => 
                        [
                           [
                                "typeId" => 7,
                                "value" => $groupOnOrder->orderid
                           ],
                        ],
                        "relations" =>
                        [
                            [
                                "referenceType" => "contact",
                                "relation" => "receiver",
                                "referenceId"=>$customer->id
                            ],
                        ],
                        'addressRelations' => [
                            ['typeId' => 1, 'addressId' => $deliveryAddress->id],
                            ['typeId' => 2, 'addressId' => $deliveryAddress->id],
                        ],
                    ]);
                        
                    $exported = $this->markAsExported($groupOnOrder);
                   /* $saveOrder = $this->saveOrder($addOrder);*/
                    return $addOrder;
                }
            }
            return null;
        });
        return $order;
    }
    
    
    
}