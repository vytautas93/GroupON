<?php

namespace Groupon\Crons;
 
use Plenty\Plugin\ServiceProvider;
use Plenty\Modules\Cron\Contracts\CronHandler as Cron;

use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Account\Address\Contracts\AddressRepositoryContract;
use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Account\Contact\Contracts\ContactAddressRepositoryContract;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;
use Plenty\Modules\EventProcedures\Events\EventProceduresTriggered;
use Plenty\Modules\Order\Shipping\Contracts\ParcelServicePresetRepositoryContract;

use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Modules\Payment\Contracts\PaymentOrderRelationRepositoryContract;
use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;
use Groupon\Models\Expire;


use Plenty\Plugin\Log\Loggable;

class SynchronizeGroupOnOrdersCron extends Cron
{
    use Loggable;
    private $authHelper;
    
    public function __construct(
        AuthHelper $authHelper
    )
    {
        $this->authHelper = $authHelper;
    }
    
    
     public function handle()
    {
        $trial = $this->checkTrial();
       
        if ($trial) 
        {
            $configurations = $this->getConfiguration();
            if(!empty($configurations))
            {
                foreach ($configurations as $country => $configuration) 
                {
                    $pageNumber = $this->getPageNumber($configuration);
                    if((int)$pageNumber>0) 
                    {
                        for ($i = 1; $i <= (int)$pageNumber; $i++) 
                        {
                            $groupOnOrders = $this->getGroupOnOrders($configuration,$i);
                            foreach($groupOnOrders as $groupOnOrder)
                            {
                                $exists = $this->checkIfExists($country,$groupOnOrder->orderid);
                                if ($exists == false) 
                                {   
                                    $order = $this->generateOrder($country,$configuration,$groupOnOrder);
                                }
                            }
                        }
                    }
                    else
                    {
                        $this->getLogger(__FUNCTION__)->error("Groupon Data","There are no orders for $country groupon");
                    }
                }
            }
            else
            {
                 $this->getLogger(__FUNCTION__)->error("Configurations","Please enter required configurations");
            }
        }
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
        if($response) 
        {
          $response_json = json_decode( $response );
          if( $response_json->success == true ) {
          } else {
            
          }
        }
       return $response;
    }


    public function getGroupOnOrders($configuration,$page)
    {
        $url = 'https://scm.commerceinterface.com/api/v4/get_orders?supplier_id='.$configuration['supplierID'].'&token='.$configuration['token'].'&page='.$page;
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
        $variationSkuRepositoryContract = pluginApp(VariationSkuRepositoryContract::class);
        $configRepository = pluginApp(ConfigRepository::class);
        foreach($groupOnItems as $groupOnItem)
        {
            try 
            {
                $findVariationID = $variationSkuRepositoryContract->search(
                    array(
                        "sku" => $groupOnItem->sku,
                        "marketId" => $configRepository->get("Groupon.referrerID"),
                ));    
            } 
            catch (\Exception $e) 
            {
               $this->getLogger(__FUNCTION__)->error("Something went wrong!",$e->getMessage());   
            }
            
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
                    'referrerId' => $configRepository->get("Groupon.referrerID"),
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
                $this->getLogger(__FUNCTION__)->error("Please Add missing SKU to your Items","SKU:$groupOnItem->sku");
                return null;    
            }
        }
        return $orderItems;
    }
    

    public function createDeliveryAddress($groupOnOrder,$customer,$country)
    {
        $addressRepositoryContract = pluginApp(AddressRepositoryContract::class);
        $countryRepositoryContract = pluginApp(CountryRepositoryContract::class);
        $contactAddressRepositoryContract = pluginApp(ContactAddressRepositoryContract::class);
        
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
        
        try 
        {
            $country = $countryRepositoryContract->getCountryByIso($countryISO,"isoCode2");
            $deliveryAddress = $addressRepositoryContract->createAddress([
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
        } 
        catch (\Exception $e) 
        {
            $this->getLogger(__FUNCTION__)->error("Something went wrong!",$e->getMessage());   
        }
        
       
        if(isset($deliveryAddress->id) && isset($customer->id))
        {
            try 
            {
                $addContactAddress = $contactAddressRepositoryContract->addAddress($deliveryAddress->id,$customer->id,2);
                return $deliveryAddress;    
            } 
            catch (\Exception $e) 
            {
                $this->getLogger(__FUNCTION__)->error("Something went wrong!",$e->getMessage());   
            }
        }
        else
        {
            return null;
        }
        
    }
    
    public function createCustomer($groupOnOrder)
    {
        $contactRepositoryContract = pluginApp(ContactRepositoryContract::class);
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

        try 
        {
            $customer = $contactRepositoryContract->createContact($data); 
            if(isset($customer->id))
            {
                return $customer;        
            }
            else
            {
                return null;
            }
        } 
        catch (\Exception $e) 
        {
            $this->getLogger(__FUNCTION__)->error("Something went wrong!",$e->getMessage());   
        }
        
    }
    
    
    public function Procedure(EventProceduresTriggered $eventTriggered)
    {
        $configRepository = pluginApp(ConfigRepository::class);
        $order = $eventTriggered->getOrder();
        $parameters = [];
        foreach ($order->properties as $config) {
            if((int)$config->typeId == 2)
            {
                 $preset = pluginApp(ParcelServicePresetRepositoryContract::class);
                 try 
                 {
                    $shippingProfile = $preset->getPresetById($config->value);
                    //$carrier = $shippingProfile->parcelService->backend_name;    
                    $carrier = "DHL Germany";    
                 } 
                 catch (\Exception $e) 
                 {
                    $this->getLogger(__FUNCTION__)->error("Something went wrong!",$e->getMessage());   
                 }  
                 
            }
            if((int)$config->typeId == 7)
            {
                $countryISO = substr($config->value, 0, 2);
                $supplierID = $configRepository->get("Groupon.$countryISO-supplierID");
                $token = $configRepository->get("Groupon.$countryISO-token");  
            }
        }
        
        if ($carrier && $supplierID && $token) 
        {

            $datatopost = $this->formateFeedBack($order,$carrier,$supplierID,$token);
            $this->getLogger(__FUNCTION__)->error('Feedback',json_encode($datatopost)); 
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
                    
                    $this->getLogger(__FUNCTION__)->error('Succesfull response From Groupon',"FeedBack was sended\n.$response"); 
                  } 
                  else 
                  {
                    $this->getLogger(__FUNCTION__)->error('Bad Response From Groupon',"Something was wrong\n.$response"); 
                  }
                }        
            }
        }
        
        else
        {
            $this->getLogger(__FUNCTION__)->error('Missing Parameters',"Add missing parameters"); 
        }
    }
    
    
    public function formateFeedBack($order,$carrier,$supplierID,$token)
    {
        $orderRepositoryContract = pluginApp(OrderRepositoryContract::class);
        
        $lineItemIds = [];
        try 
        {
            $packageNumber = $orderRepositoryContract->getPackageNumbers($order->id);
        } 
        catch (\Exception $e) 
        {
            $this->getLogger(__FUNCTION__)->error("Something went wrong!",$e->getMessage());   
        }
        
        foreach($order->orderItems as $orderItems)
        {
            foreach($orderItems->properties as $properties)
            {
                if((int)$properties->typeId == 17)
                {
                    $lineItemIds[] = 
                    [
                        "quantity" => $orderItems->quantity,
                        "carrier" => $carrier,
                        "ci_lineitem_id" => $properties->value,
                        "tracking" => $packageNumber[0]
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
        return $address;
    }
    
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
            $orderRepositoryContract = pluginApp(OrderRepositoryContract::class);
            $configRepository = pluginApp(ConfigRepository::class);
            $customer = $this->createCustomer($groupOnOrder);
            $deliveryAddress = $this->createDeliveryAddress($groupOnOrder,$customer,$country);
            if(!is_null($customer) && !is_null($deliveryAddress))
            {
                $orderItems = $this->generateOrderItemLists($groupOnOrder->line_items);
                if (!is_null($orderItems)) 
                {
                    try 
                    {
                        $time = date('Y-m-d G:i:s',strtotime($groupOnOrder->date));
                        
                        $addOrder = $orderRepositoryContract->createOrder(
                        [
                            'typeId' => 1,
                            'methodOfPaymentId' => $configRepository->get("Groupon.payment"),
                            'shippingProfileId' => 6,
                            'createdAt' => date('Y-m-d G:i:s',strtotime($time)),
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
                        ]);

                        $paymentOrderRelationRepositoryContract = pluginApp(PaymentOrderRelationRepositoryContract::class);
                        $paymentRepositoryContract = pluginApp(PaymentRepositoryContract::class);
                        $data = 
                            [
                                "amount" => $groupOnOrder->amount->total,
                                "origin" => 6,
                                "receivedAt" => date('Y-m-d G:i:s',strtotime($time)),
                                "currency" => "EUR",
                                "status" => 2,
                                "transactionType" => 1,
                                "properties" => 
                                [
                                    ["typeId" =>1 ,"value" =>$country.$groupOnOrder->orderid],
                                ],
                                "mopId" => $configRepository->get("Groupon.payment")
                            ];
                        $createPayment = $paymentRepositoryContract->createPayment($data);
                        $orderRelation = $paymentOrderRelationRepositoryContract->createOrderRelation($createPayment,$addOrder);    
                    }
                    catch (\Exception $e) 
                    {
                        $this->getLogger(__FUNCTION__)->error("Something went wrong!",$e->getMessage());   
                    }
                    $exported = $this->markAsExported($groupOnOrder,$configuration);
                    return $addOrder;
                }
            }
            return null;
        });
        return $order;
    }
    
    public function getConfiguration()
    {
        $configRepository = pluginApp(ConfigRepository::class);
        
        $countryArrays = ["DE","FR","IT"];
        $configurationArray = [];
        foreach($countryArrays as $country)
        {
            try 
            {
                $supplierID = $configRepository->get("Groupon.$country-supplierID");
                $token = $configRepository->get("Groupon.$country-token");
            } 
            catch (\Exception $e) 
            {
                $this->getLogger(__FUNCTION__)->error("Something went wrong!",$e->getMessage());   
            }
            
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
    
    public function checkTrial()
    {
        $database = pluginApp(DataBase::class);
        $expire = $database->query(Expire::class)->get();
        if ($expire) {
            if ((int)$expire[0]->expiredtime > time()) 
            {
                $willExpire = date("Y-m-d H:i:s",(int)$expire[0]->expiredtime);
                $this->getLogger(__FUNCTION__)->error("Trial version","Your trial will expire on $willExpire");   
                return true;
            } 
            else
            {
                $this->getLogger(__FUNCTION__)->error("Trial Expired","Your Trial expired, Please buy Full version of Groupon Plugin");   
                return false;
            }
        }
        else
        {  
            $expiredTime = (int)strtotime('+1 months');
            $model = pluginApp(Expire::class);
            $model->expiredtime = $expiredTime;
            try 
            {
                $database->save($model);
                return true;
            } 
            catch (\Exception $e) 
            {
                $this->getLogger(__FUNCTION__)->error("Database Error",json_encode($e->getMessage()));   
            }  
        }
    }
    
    public function getPageNumber($configuration)
    {
        $url = 'https://scm.commerceinterface.com/api/v4/get_orders?supplier_id='.$configuration['supplierID'].'&token='.$configuration['token'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch); 
        curl_close($ch);      
        $groupOnData = json_decode($response);
        $page = $groupOnData->meta->no_of_pages;
        if ($page) 
        {
            return $page;
        }
        else return 0;
    }
}