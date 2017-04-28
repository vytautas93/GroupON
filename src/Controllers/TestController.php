<?php
 
namespace Groupon\Controllers;
 
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;

use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;
use Groupon\Models\StartTime;

use Plenty\Plugin\Log\Loggable;

class TestController extends Controller
{
    use Loggable;
    public function checkTrial()
    {
        $database = pluginApp(DataBase::class);
        $startTime = $database->query(StartTime::class)->get();
        $delete = $database->delete(StartTime::class);
        $this->getLogger(__FUNCTION__)->error("Database",json_encode($delete));   
       /* if ($startTime) {
             
           
              $time = (string)strtotime('+1 months');
            
            
            
            
            $this->getLogger(__FUNCTION__)->error("Database in IF",json_encode($startTime));   
            $this->getLogger(__FUNCTION__)->error("time",json_encode($time));   
        }
        else
        {  
             $time = (string)strtotime('+1 months');
             $this->getLogger(__FUNCTION__)->error("Time",json_encode($time));   
             $model = pluginApp(StartTime::class);
             $model->startTime = $time;
             $test = $database->save($model);
             $this->getLogger(__FUNCTION__)->error("Database in Else",json_encode($test));   
        }*/
        
        
        
       
    }
}