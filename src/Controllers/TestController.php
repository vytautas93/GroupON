<?php
 
namespace GroupON\Controllers;


use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Templates\Twig;
use Plenty\Modules\EventProcedures\Services\Entries\ProcedureEntry;


class TestController extends Controller
{
    private $entry; 
    public function __construct(
        ProcedureEntry $entry
    )
    {
        $this->entry = $entry;
    }
    
    public function event(Twig $twig)
    {
        
        $test = $this->entry->getModuleName();
        
        $templateData = array("supplierID" => json_encode($test));
        return $twig->render('GroupON::content.test',$templateData);
    }
}