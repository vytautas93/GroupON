<?php
 
namespace GroupON\Controllers;
 
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Templates\Twig;
use GroupON\Contracts\GroupOnRepositoryContract;

use Plenty\Plugin\ConfigRepository;
/**
 * Class ContentController
 * @package ToDoList\Controllers
 */
class ContentController extends Controller
{
    /**
     * @param Twig                   $twig
     * @param ToDoRepositoryContract $toDoRepo
     * @return string
     */
     
    public function test(Twig $twig,ConfigRepository $configRepository ):string
    {
        $supplierID = $configRepository->get('GroupON.supplierID');
 
        if(!strlen($supplierID))
        {
            $supplierID = 'Enter Supplier ID';
        }
        $templateData = array("supplierID" => $supplierID);
       
        
        return $twig->render('GroupON::content.test',$templateData);
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