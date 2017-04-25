<?php
 
namespace GroupON\Migrations;
 
use GroupON\Models\Groupon;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;
 
/**
 * Class CreateToDoTable
 */
class CreateGroupONTable
{
    /**
     * @param Migrate $migrate
     */
    public function run(Migrate $migrate)
    {
        $migrate->createTable(Groupon::class);
    }
}