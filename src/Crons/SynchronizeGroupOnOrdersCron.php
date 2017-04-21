<?php

namespace GroupON\Crons;
 
use Plenty\Plugin\ServiceProvider;
use Plenty\Modules\Cron\Contracts\CronHandler as Cron;

use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Account\Address\Contracts\AddressRepositoryContract;
use Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract; 



use Plenty\Plugin\Log\Loggable;

class SynchronizeGroupOnOrdersCron extends Cron
{
    use Loggable;
    
    private $orderRepository;
    private $addressRepository;
    private $configRepository;
    private $countryRepositoryContract;
    private $variationSkuRepositoryContract;
    private $contactRepositoryContract;
    private $authHelper;
    
    public function __construct(
        OrderRepositoryContract $orderRepository,
        AddressRepositoryContract $addressRepository,
        ConfigRepository $configRepository,
        CountryRepositoryContract $countryRepositoryContract,
        VariationSkuRepositoryContract $variationSkuRepositoryContract,
        ContactRepositoryContract $contactRepositoryContract,
        AuthHelper $authHelper
    )
    {
        $this->orderRepository = $orderRepository;
        $this->addressRepository = $addressRepository;
        $this->configRepository = $configRepository;
        $this->countryRepositoryContract = $countryRepositoryContract;
        $this->variationSkuRepositoryContract = $variationSkuRepositoryContract;
        $this->contactRepositoryContract = $contactRepositoryContract;
        $this->authHelper = $authHelper;
    }

    
    public function handle()
    {
        $this->getLogger(__FUNCTION__)->error('Cron',"Cron is trigered"); 
        $groupOnOrders = $this->getGroupOnOrders();
        foreach($groupOnOrders as $groupOnOrder)
        {
            $orders = $this->authHelper->processUnguarded(
            function () use ($groupOnOrder) 
            {
                $customer = $this->createCustomer($groupOnOrder);
                $deliveryAddress = $this->createDeliveryAddress($groupOnOrder);
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
                        'addressRelations' => [
                            ['typeId' => 1, 'addressId' => $deliveryAddress->id],
                            ['typeId' => 2, 'addressId' => $deliveryAddress->id],
                        ]    
                    ]);
                    
                    $exported = $this->markAsExported($groupOnOrder);

                    return $addOrder;
                }
                return null;
            });
        }
        
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
        $url = 'https://scm.commerceinterface.com/api/v2/get_orders?supplier_id='.$supplierID.'&token='.$token.'&start_datetime=04/11/2017+00:00&end_datetime=04/11/2017+23:59';
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
                    'amounts' => $amounts
                ];    
            }
            else
            {
                return null;    
            }
        }
        return $orderItems;
    }
    

    public function createDeliveryAddress($groupOnOrder)
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

        return $deliveryAddress;
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
        return $customer;        
    }
}