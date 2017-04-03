<?php
 
namespace GroupON\Migrations;
 
use GroupON\Models\GroupOn;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;
 
/**
 * Class CreateToDoTable
 */
class CreateGroupOnTable
{
    /**
     * @param Migrate $migrate
     */
    public function run(Migrate $migrate)
    {
        $migrate->createTable(GroupOn::class);
    }
}