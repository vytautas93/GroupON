<?php
 
namespace Groupon\Providers;
 
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
        $router->get('test', 'Groupon\src\Crons\SynchronizeGroupOnOrdersCron@checkTrial');
    }
 
}