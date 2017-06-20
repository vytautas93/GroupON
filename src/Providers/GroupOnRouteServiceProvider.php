<?php
 
namespace Groupon\Providers;
 
use Plenty\Plugin\RouteServiceProvider;

use Plenty\Plugin\Routing\Router;

class GroupOnRouteServiceProvider extends RouteServiceProvider
{
    public function map(Router $router)
    {
        $router->get('groupon','Crowdfox\Controllers\OrdersController@handle');
        
    }
}