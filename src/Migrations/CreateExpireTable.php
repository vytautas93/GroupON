<?php
 
namespace Groupon\Migrations;
 
use Groupon\Models\Expire;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;
 
use Plenty\Plugin\Log\Loggable;
class CreateGrouponTable
{
    use Loggable;
    public function run(Migrate $migrate)
    {
        $migrate->createTable(Expire::class);
    }
}