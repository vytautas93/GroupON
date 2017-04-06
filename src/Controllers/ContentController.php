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

/**
 * Class ContentController
 * @package ToDoList\Controllers
 */
class ContentController extends Controller
{
    
    private $orderRepository;
    private $addressRepository;
    private $authHelper;
    //private $addressRepository;
    
    public function __construct(
        OrderRepositoryContract $orderRepository,
        AddressRepositoryContract $addressRepository,
        AuthHelper $authHelper
    )
    {
        $this->orderRepository = $orderRepository;
        $this->addressRepository = $addressRepository;
        $this->authHelper = $authHelper;
    }
    
    /**
     * @param Twig                   $twig
     * @param ToDoRepositoryContract $toDoRepo
     * @return string
     */
    
    
    
    public function test(Twig $twig, ConfigRepository $configRepository ):string
    {

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
                
           /*     $amounts = [];
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

                $data = array
                (
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
                );
                $addOrder = $this->orderRepository->createOrder($data,null);*/
                return $deliveryAddress;
            }
        );
        
        $templateData = array("supplierID" => json_decode($order));
        return $twig->render('GroupON::content.test',$templateData);
        
       
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