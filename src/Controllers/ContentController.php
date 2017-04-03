<?php
 
namespace GroupON\Controllers;
 
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Templates\Twig;
use GroupON\Contracts\GroupOnRepositoryContract;
 
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