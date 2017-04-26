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
        $configurations = $this->getConfiguration();
        if(!empty($configurations))
        {
            foreach ($configurations as $country => $configuration) 
            {
                $groupOnOrders = $this->getGroupOnOrders($configuration);
                foreach($groupOnOrders as $groupOnOrder)
                {
                    $exists = $this->checkIfExists($country,$groupOnOrder->orderid);
                    if ($exists == false) 
                    {   
                        $order = $this->generateOrder($country,$configuration,$groupOnOrder);
                    }
                    else
                    {
                        $order = 'Nesukurti';
                    }
                }
            }
        }
        
        
        
        
        
        $templateData = array("supplierID" => json_encode($exists));
        return $twig->render('GroupON::content.test',$templateData);
    }    
    
    public function markAsExported($groupOnOrder,$configuration)
    {   
        $lineItemsIds = [];
        foreach($groupOnOrder->line_items as $item)
        {
            $lineItemsIds[] = $item->ci_lineitemid;
        }
        
        $datatopost = array (
            "supplier_id" => $configuration['supplierID'],
            "token" => $configuration['token'],
            "ci_lineitem_ids" => json_encode ($lineItemsIds),
        );
        
        
        $ch = curl_init ("https://scm.commerceinterface.com/api/v4/mark_exported");
        curl_setopt ($ch, CURLOPT_POST, true);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $datatopost);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec ($ch);
        $this->getLogger(__FUNCTION__)->info('Response From GroupON',json_encode($response));
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


    public function getGroupOnOrders($configuration)
    {
        $url = 'https://scm.commerceinterface.com/api/v4/get_orders?supplier_id='.$configuration['supplierID'].'&token='.$configuration['token'].'&start_datetime=03/01/2017+12:01&end_datetime=03/01/2017+15:00';
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
            $findVariationID = $this->variationSkuRepositoryContract->search(
                array(
                    "sku" => $groupOnItem->sku,
                    "marketId" => (int)$this->configRepository->get("GroupON.referrerID"),
                ));
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
                    'referrerId' => (int)$this->configRepository->get("GroupON.referrerID"),
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
    

    public function createDeliveryAddress($groupOnOrder,$customer,$country)
    {
        $countryISO = $groupOnOrder->customer->country;
        
        if(empty($countryISO))
        {
          $countryISO  = $country;
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
                $this->getLogger(__FUNCTION__)->info('Succesfull response From GroupON',"FeedBack was sended\n.$response"); 
              } 
              else 
              {
                $this->getLogger(__FUNCTION__)->error('Bad Response From GroupON',"Something was wrong\n.$response"); 
              }
            }        
        }
    }
    
    
    public function formateFeedBack($order)
    {
        $lineItemIds = [];
        $packageNumber = $this->orderRepository->getPackageNumbers($order->id);
        foreach ($order->properties as $config) 
        {
            if((int)$properties->typeId == 7)
            {
                $countryISO = substr($properties->value, 0, 2);
                $supplierID = $this->configRepository->get("GroupON.$countryISO-supplierID");
                $token = $this->configRepository->get("GroupON.$countryISO-token");  
                foreach($order->orderItems as $orderItems)
                {
                    foreach($orderItems->properties as $properties)
                    {
                        if((int)$properties->typeId == 17)
                        {
                            $lineItemIds[] = 
                            [
                                "ci_lineitem_id" => $properties->value,
                                "carrier" => "UPS",
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
                
                
            }
        }
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
    
   /* public function saveOrder($orderData)
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
    }*/
    
    public function checkIfExists($country,$orderID)
    {
        $exist = $this->authHelper->processUnguarded(
        function () use ($orderID,$country) 
        {
            $contract = pluginApp(OrderRepositoryContract::class);
            $setFilter = $contract->setFilters(['externalOrderId' => (string)$country.$orderID ]);
            $orderList = $contract->searchOrders();
            $totalsCount = json_decode(json_encode($orderList),true);
            if($totalsCount['totalsCount'] > 0)
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
    
    public function generateOrder($country,$configuration,$groupOnOrder)
    {
        $order = $this->authHelper->processUnguarded(
        function () use ($groupOnOrder,$configuration,$country) 
        {
            $customer = $this->createCustomer($groupOnOrder);
            $deliveryAddress = $this->createDeliveryAddress($groupOnOrder,$customer,$country);
            if(!is_null($customer) && !is_null($deliveryAddress))
            {
                $orderItems = $this->generateOrderItemLists($groupOnOrder->line_items);
                if (!is_null($orderItems)) 
                {
                    $addOrder = $this->orderRepository->createOrder(
                    [
                        'typeId' => 1,
                        'methodOfPaymentId' => (int)$this->configRepository->get("GroupON.payment"),
                        'shippingProfileId' => 6,
                        'plentyId' => 0,
                        'orderItems' => $orderItems,
                        'properties' => 
                        [
                           [
                                "typeId" => 7,
                                "value" => $country.$groupOnOrder->orderid
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
                        'dates'=>
                        [
                          ['typeId' => 3 , 'createdAt' => $groupOnOrder->date], 
                          ['typeId' => 7 , 'createdAt' => $groupOnOrder->date],  
                        ],
                    ]);
                        
                    $exported = $this->markAsExported($groupOnOrder,$configuration);
                   /* $saveOrder = $this->saveOrder($addOrder);*/
                    return $addOrder;
                }
            }
            return null;
        });
        return $order;
    }
    
    public function getConfiguration()
    {
        $countryArrays = ["DE","FR","IT"];
        $configurationArray = [];
        foreach($countryArrays as $country)
        {
            $supplierID = $this->configRepository->get("GroupON.$country-supplierID");
            $token = $this->configRepository->get("GroupON.$country-token");
            if(!empty($supplierID) && !empty($token))
            {
                $configurationArray[$country] = 
                [
                    "supplierID" => $supplierID,
                    "token" => $token
                ];        
            }
        }
        return $configurationArray;
    }
    
}