<?php
 
namespace Groupon\Migrations;
 
use Groupon\Models\Groupon;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;
 

class CreateGrouponTable
{

    public function run(Migrate $migrate)
    {
        $migrate->createTable(Groupon::class);
    }
}