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

                        return $addOrder;
                    }
                }
                return null;
            });
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
        $url = 'https://scm.commerceinterface.com/api/v2/get_orders?supplier_id='.$supplierID.'&token='.$token.'&start_datetime=04/09/2017+00:00&end_datetime=04/09/2017+07:50';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch); 
        curl_close($ch);      
        $groupOnData = json_decode($response);
        
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
        $country = $this->countryRepositoryContract->getCountryByIso($countryISO,"isoCode2");
        $deliveryAddress = $this->addressRepository->createAddress([
            'name2' => $groupOnOrder->customer->name,
            'address1' => $groupOnOrder->customer->address1,
            'address2' => $groupOnOrder->customer->address2,
            'town' => $groupOnOrder->customer->city,
            'postalCode' => $groupOnOrder->customer->zip,
            'countryId' => $country->id,
            'phone' => $groupOnOrder->customer->phone
        ]);
        
        if(isset($deliveryAddress->id) && isset($customer->id))
        {
            $addContactAddress = $this->contactAddressRepositoryContract->addAddress($deliveryAddress->id,$customer->id,2);
            return $deliveryAddress;
        }
        else
        {
            return null;
        }
        
    }
    
    public function createCustomer($groupOnOrder)
    {
        $data = 
        [
            'typeId'=>1,
            
            'firstName' => $groupOnOrder->customer->name,
            'formOfAddress' => 0,
            'lang'=>'de',
            'referrerId' => 1,
            'plentyId' => 0,
            'privatePhone' => $groupOnOrder->customer->phone,
        ];

      
        $customer = $this->contactRepositoryContract->createContact($data); 
        if(isset($customer->id))
        {
            return $customer;        
        }
        else
        {
            return null;
        }
    }
    
    
    public function Procedure()
    {
           $this->getLogger(__FUNCTION__)->error('Procedure method', "Works");  
    }
    
}