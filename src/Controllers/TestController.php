<?php
 
namespace GroupON\Controllers;


use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Templates\Twig;
use PPlenty\Modules\EventProcedures\Services\EventProceduresService;


class TestController extends Controller
{
    private $procedure; 
    public function __construct(
        EventProceduresService $procedure
    )
    {
        $this->procedure = $procedure;
    }
    
    public function event(Twig $twig)
    {
        
        $test = $this->procedure->registerProcedure(
            "Send Feedback" ,
            ProcedureEntry::EVENT_TYPE_ORDER,
            [
                'de' => 'Send Feedback DE',
                'en' => 'Send Feedback',
            ],
            'GroupON\\Controllers\\TestController@sendFeedback'
        );

        $templateData = array("supplierID" => json_encode($test));
        return $twig->render('GroupON::content.test',$templateData);
    }
}