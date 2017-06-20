<?php
 
namespace Groupon\Providers;
 
use Plenty\Plugin\RouteServiceProvider;

use Plenty\Plugin\Routing\Router;

class GroupOnRouteServiceProvider extends RouteServiceProvider
{
    public function map(Router $router)
    {
        $router->get('groupon','Groupon\Controllers\OrdersController@handle');
        
    }
}