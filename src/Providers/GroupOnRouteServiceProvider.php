<?php
 
namespace Groupon\Providers;
 
use Plenty\Plugin\RouteServiceProvider;

use Plenty\Plugin\Routing\Router;

class GroupOnRouteServiceProvider extends RouteServiceProvider
{
    public function map(Router $router)
    {
        $router->post('groupon','Groupon\Controllers\GrouponController@handle');
        $router->post('trial','Groupon\Controllers\GrouponController@trial');
        
    }
}