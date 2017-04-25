<?php
 
namespace GroupON\Migrations;
 
use GroupON\Models\GroupON;
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
        $migrate->createTable(GroupON::class);
    }
}