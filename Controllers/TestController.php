<?php
 
namespace Groupon\Controllers;
 
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Templates\Twig;

use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;
use Groupon\Models\GrouponModel;

use Plenty\Plugin\Log\Loggable;

class TestController extends Controller
{
    use Loggable;
     public function checkTrial()
    {
        $database = pluginApp(DataBase::class);
        $startTime = $database->query(GrouponModel::class)->get();
        $this->getLogger(__FUNCTION__)->error("Database",json_encode($startTime));   
        if ($startTime) {
            $this->getLogger(__FUNCTION__)->error("Database in IF",json_encode($startTime));   
        }
        /*else
        {
             $model = pluginApp(GrouponModel::class);
             $model->createdAt = time();
             $database->save($model);
        }*/
        
        
        
       
    }
}