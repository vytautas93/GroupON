<?php
 
namespace Groupon\Migrations;
 
use Groupon\Models\StartTime;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;
 
use Plenty\Plugin\Log\Loggable;
class CreateGrouponTable
{
   use Loggable;
    public function run(Migrate $migrate)
    {
       /* $migrate->createTable(StartTime::class);*/
        $test = $migrate->deleteTable(StartTime::class);
        $this->getLogger(__FUNCTION__)->error("Migration",json_encode($test));   
    }
}