<?php
 
namespace GroupON\Controllers;
 
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;

use Plenty\Modules\Authorization\Services\AuthHelper;

use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Templates\Twig;

use GroupON\Contracts\GroupOnRepositoryContract;

use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Account\Address\Contracts\AddressRepositoryContract;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract;

use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;

class ContentController extends Controller
{
    
    private $orderRepository;
    private $addressRepository;
    private $configRepository;
    private $countryRepositoryContract;
    private $variationLookupRepositoryContract;
    private $authHelper;
    
    public function __construct(
        OrderRepositoryContract $orderRepository,
        AddressRepositoryContract $addressRepository,
        ConfigRepository $configRepository,
        CountryRepositoryContract $countryRepositoryContract,
        VariationSkuRepositoryContract $variationSkuRepositoryContract,
        AuthHelper $authHelper
    )
    {
        $this->orderRepository = $orderRepository;
        $this->addressRepository = $addressRepository;
        $this->configRepository = $configRepository;
        $this->countryRepositoryContract = $countryRepositoryContract;
        $this->variationSkuRepositoryContract = $variationSkuRepositoryContract;
        $this->authHelper = $authHelper;
    }
    
    public function test(Twig $twig):string
    {
        $groupOnOrders = $this->getGroupOnOrders();
        foreach($groupOnOrders as $groupOnOrder)
        {
            $order = $this->authHelper->processUnguarded(
                function () use ($order,$groupOnOrder) 
                {
                    $countryISO = $groupOnOrder->customer->country;
                    $country = $this->countryRepositoryContract->getCountryByIso($countryISO,"isoCode2");
                    $deliveryAddress = $this->addressRepository->createAddress([
                        'name1' => $groupOnOrder->customer->name,
                        'address1' => $groupOnOrder->customer->address1,
                        'address2' => $groupOnOrder->customer->address2,
                        'town' => $groupOnOrder->customer->city,
                        'postalCode' => $groupOnOrder->customer->zip,
                        'countryId' => $country->id,
                        'phone' => $groupOnOrder->customer->phone
                    ]);
                    $orderItems = $this->generateOrderItemLists($groupOnOrder->line_items);
                    if (!isset($orderItems)) {
                        $addOrder = $this->orderRepository->createOrder(
                        [
                            'typeId' => 1,
                            'methodOfPaymentId' => 4040,
                            'shippingProfileId' => 6,
                            'statusId' => 5.0, 
                            'ownerId' => 107,
                            'plentyId' => 0,
                            'orderItems' => $orderItems,
                            'addressRelations' => [
                                ['typeId' => 1, 'addressId' => $deliveryAddress->id],
                                ['typeId' => 2, 'addressId' => $deliveryAddress->id],
                            ]    
                        ]);
                        
                        return $addOrder;
                    }
                    return null;
                }
            );
        }
        
        $templateData = array("supplierID" => json_encode($order));
        return $twig->render('GroupON::content.test',$templateData);
    }    

    public function getGroupOnOrders()
    {
        $supplierID = $this->configRepository->get('GroupON.supplierID');
        $token = $this->configRepository->get('GroupON.token');
        $url = 'https://scm.commerceinterface.com/api/v2/get_orders?supplier_id='.$supplierID.'&token='.$token;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch); 
        curl_close($ch);      
        $groupOnData = json_decode($response);
        
        return $groupOnData->data;
    }
    
    public function generateOrderItemLists(array $groupOnItems)
    {
        $amounts = [];
        $orderItems = [];
        foreach($groupOnItems as $groupOnItem)
        {
            $findVariationID = $this->variationSkuRepositoryContract->search(array("sku" => $groupOnItem->sku));
            if (!empty($findVariationID)){
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
            
                return $orderItems;
            }
            return null;
        }
        
    }
    
    

    public function showGroupOnUser(Twig $twig, GroupOnRepositoryContract $groupOnRepo): string
    {
        $groupOnUserList = $groupOnRepo->getGroupOnList();
        $templateData = array("groupOnUsers" => $groupOnUserList);
        return $twig->render('GroupON::content.groupOnUsers', $templateData);
    }
 
    /**
     * @param  \Plenty\Plugin\Http\Request $request
     * @param ToDoRepositoryContract       $toDoRepo
     * @return string
     */
    public function createGroupOnUser(Request $request, GroupOnRepositoryContract $groupOnRepo): string
    {
        $newGroupOnUser = $groupOnRepo->createGroupOnUser($request->all());
        return json_encode($newGroupOnUser);
    }
 
    /**
     * @param int                    $id
     * @param ToDoRepositoryContract $toDoRepo
     * @return string
     */
    public function updateGroupOnUser(int $id, GroupOnRepositoryContract $groupOnRepo): string
    {
        $updateGroupOnUser = $groupOnRepo->updateGroupOnUser($id);
        return json_encode($updateGroupOnUser);
    }
 
    /**
     * @param int                    $id
     * @param ToDoRepositoryContract $toDoRepo
     * @return string
     */
    public function deleteGroupOnUser(int $id, GroupOnRepositoryContract $groupOnRepo): string
    {
        $deleteGroupOnUser = $groupOnRepo->deleteTask($id);
        return json_encode($deleteGroupOnUser);
    }
}