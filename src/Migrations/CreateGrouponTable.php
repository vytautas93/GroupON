<?php
 
namespace GroupON\Migrations;
 
use GroupON\Models\Groupon;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;
 
/**
 * Class CreateToDoTable
 */
class CreateGrouponTable
{
    /**
     * @param Migrate $migrate
     */
    public function run(Migrate $migrate)
    {
        $migrate->createTable(Groupon::class);
    }
}