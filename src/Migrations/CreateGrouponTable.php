<?php
 
namespace Groupon\Migrations;
 
use Groupon\Models\StartTime;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;
 

class CreateGrouponTable
{

    public function run(Migrate $migrate)
    {
        $migrate->createTable(StartTime::class);
    }
}