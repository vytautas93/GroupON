<?php
 
namespace GroupON\Controllers;


use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;


class TestController extends Controller
{
    
    public function event(Twig $twig, Dispatcher $dispatcher)
    {
        $test = $dispatcher->listen(OrderCreated::class,function()
        {
            $templateData = array("supplierID" => json_encode($test));
            return $twig->render('GroupON::content.test',$templateData);
            
        });
       
    }
}