<?php
 
namespace Groupon\Controllers;
 
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;

use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;
use Groupon\Models\Expire;

use Plenty\Plugin\Log\Loggable;

class TestController extends Controller
{
    use Loggable;
    public function checkTrial()
    {
        $database = pluginApp(DataBase::class);
        $startTime = $database->query(Expire::class)->get();
        
        if ($startTime) {
            
            if ($startTime[0]->expireTime > time()) 
            {
                return true;
            } 
            else
            {
                $this->getLogger(__FUNCTION__)->info("Trial Expired","Your Trial expired, Please buy Full version of Groupon Plugin");   
                return false;
            }
        }
        else
        {  
             $expireTime = (int)strtotime('+1 months');
             $model = pluginApp(Expire::class);
             $model->expiredtime = $expireTime;
             $save = $database->save($model);
             $this->getLogger(__FUNCTION__)->error("Database in Else",json_encode($save));   
        }
    }
}