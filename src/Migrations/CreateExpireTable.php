<?php
 
namespace Groupon\Migrations;
 
use Groupon\Models\Expire;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;

class CreateGrouponTable
{
    public function run(Migrate $migrate)
    {
        $migrate->createTable(Expire::class);
    }
}