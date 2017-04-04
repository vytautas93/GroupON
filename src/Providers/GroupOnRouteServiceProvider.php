<?php
 
namespace GroupON\Providers;
 
use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\Router;
 
/**
 * Class ToDoRouteServiceProvider
 * @package ToDoList\Providers
 */
class GroupOnRouteServiceProvider extends RouteServiceProvider
{
    /**
     * @param Router $router
     */
    public function map(Router $router)
    {
        $router->get('test', 'GroupON\Controllers\ContentController@test');
        $router->get('group-on', 'GroupON\Controllers\ContentController@showGroupOnUser');
        
        $router->post('group-on', 'GroupON\Controllers\ContentController@createGroupOnUser');
        $router->put('group-on/{id}', 'GroupON\Controllers\ContentController@updateGroupOnUser')->where('id', '\d+');
        $router->delete('group-on/{id}', 'GroupON\Controllers\ContentController@deleteGroupOnUser')->where('id', '\d+');
    }
 
}