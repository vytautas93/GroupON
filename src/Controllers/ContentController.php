<?php
 
namespace GroupON\Controllers;
 
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;

use Plenty\Modules\Account\Address\Contracts\AddressRepositoryContract;
use Plenty\Modules\Authorization\Services\AuthHelper;

use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Templates\Twig;

use GroupON\Contracts\GroupOnRepositoryContract;

use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Models\Order;

/**
 * Class ContentController
 * @package ToDoList\Controllers
 */
class ContentController extends Controller
{
    
    private $orderRepository;
    private $authHelper;
    //private $addressRepository;
    
    public function __construct(
        OrderRepositoryContract $orderRepository,
        AuthHelper $authHelper
    )
    {
        $this->orderRepository = $orderRepository;
        $this->authHelper = $authHelper;
    }
    
    /**
     * @param Twig                   $twig
     * @param ToDoRepositoryContract $toDoRepo
     * @return string
     */
    
    
    
    public function test(Twig $twig, ConfigRepository $configRepository ):string
    {
        $data = array
        (
            "typeId" => 1,
            "ownerId" => 107
        );
        /** @var \Plenty\Modules\Authorization\Services\AuthHelper $authHelper */
       
         
        $address = null;
         
        //guarded
        $address = $this->authHelper->processUnguarded(
            function () use ($address,$data) {
                $test = $this->orderRepository->createOrder($data,null);
                return $test;
            }
        );
        
        $templateData = array("supplierID" => json_decode($address));
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