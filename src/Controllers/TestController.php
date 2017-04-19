<?php
 
namespace GroupON\Controllers;


use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Templates\Twig;
use Plenty\Modules\EventProcedures\Events\EventProceduresTriggered;


class TestController extends Controller
{
    private $event; 
    public function __construct(
        EventProceduresTriggered $event
    )
    {
        $this->event = $event;
    }
    
    public function event(Twig $twig)
    {
        
        $test = $this->event->getOrder();
        
        $templateData = array("supplierID" => json_encode($test));
        return $twig->render('GroupON::content.test',$templateData);
    }
}