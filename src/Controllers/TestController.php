<?php
 
namespace Groupon\Controllers;
 
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;

use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;
use Groupon\Models\Expire;
use Groupon\Models\StartTime;

use Plenty\Plugin\Log\Loggable;

class TestController extends Controller
{
    use Loggable;
    public function checkTrial()
    {
        $database = pluginApp(DataBase::class);
        $startTime = $database->query(StartTime::class)->get();
        $this->getLogger(__FUNCTION__)->info("Database",json_encode($startTime));   
        /*if ($startTime) {
            
            if ((int)$startTime[0]->expiredtime > time()) 
            {
                $this->getLogger(__FUNCTION__)->info("IF Statement Expired Time",json_encode($startTime[0]->expiredtime ));   
                $this->getLogger(__FUNCTION__)->info("IF Statement Time",json_encode(time()));   
                $this->getLogger(__FUNCTION__)->info("Trial Still verified","Veikia");   
                //return true;
            } 
            else
            {
                $this->getLogger(__FUNCTION__)->info("Else Statement Expired Time ",json_encode($startTime[0]->expiredtime ));   
                $this->getLogger(__FUNCTION__)->info("Else Statement Time",json_encode(time()));   
                $this->getLogger(__FUNCTION__)->info("Trial Expired","Your Trial expired, Please buy Full version of Groupon Plugin");   
                //return false;
            }
        }
        else
        {  
             $expiredTime = (int)strtotime('+1 months');
             $model = pluginApp(Expire::class);
             $model->expiredtime = $expiredTime;
             $save = $database->save($model);
             $this->getLogger(__FUNCTION__)->error("Database in Else",json_encode($save));   
        }*/
    }
}