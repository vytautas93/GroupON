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


class ContentController extends Controller
{
    
    private $orderRepository;
    private $addressRepository;
    private $authHelper;
    
    public function __construct(
        OrderRepositoryContract $orderRepository,
        AddressRepositoryContract $addressRepository,
        ConfigRepository $configRepository,
        AuthHelper $authHelper
    )
    {
        $this->orderRepository = $orderRepository;
        $this->addressRepository = $addressRepository;
        $this->configRepository = $configRepository;
        $this->authHelper = $authHelper;
    }
    
    public function test(Twig $twig):string
    {
        $groupOnOrders = $this->getGroupOnOrders();
        $templateData = array("supplierID" => json_decode($groupOnOrders->data));
        return $twig->render('GroupON::content.test',$templateData);
        
      /*  
        
        
        
        
        $order = $this->authHelper->processUnguarded(
            function () use ($order) 
            {
                
                 $deliveryAddress = $this->addressRepository->createAddress([
                    'name1' => "HashtagES",
                    'name2' => "Vytautas",
                    'name3' => "Sakalauskas",
                    'name4' => "c/o",
                    'address1' => "Pero st.",
                    'address2' => "25",
                    'address3' => "Perrui",
                    'address4' => "FreeFieldCreateSomething",
                    'postalCode' => "45874",
                    'town' => "Roma",
                    'countryId' => 1,
                    'stateId' => 1
                ]);
                
                $amounts = [];
                $amounts[] = [
                    'currency' => 'EU',
                    'priceOriginalGross' => 182.15,
                    'priceOriginalNet' => 200.14
                ];
             
                $orderItems = [];
                $orderItems[] = [
                    'typeId' => 11,
                    'quantity' => 2,
                    'orderItemName' => "HelloWorldItem",
                    'itemVariationId' => 1033,
                    'referrerId' => 1,
                    'countryVatId' => 1,
                    'amounts' => $amounts
                ];

               
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
        );
     
        $templateData = array("supplierID" => json_decode($order));
        return $twig->render('GroupON::content.test',$templateData);
           */
       
    }    
    
    
        
        
        /*$supplierID = $configRepository->get('GroupON.supplierID');
        $token = $configRepository->get('GroupON.token');
        $url = 'https://scm.commerceinterface.com/api/v2/get_orders?supplier_id='.$supplierID.'&token='.$token;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        $response = curl_exec($ch); 
        curl_close($ch);      
        $data = json_decode($response);
        $templateData = array("supplierID" => $data);

        return $twig->render('GroupON::content.test',$templateData);*/
 
    
    public function getGroupOnOrders()
    {
        $supplierID = $this->configRepository->get('GroupON.supplierID');
        $token = $this->configRepository->get('GroupON.token');
        $url = 'https://scm.commerceinterface.com/api/v2/get_orders?supplier_id='.$supplierID.'&token='.$token;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        $response = curl_exec($ch); 
        curl_close($ch);      
        $groupOnData = json_decode($response);
        
        return $groupOnData;
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