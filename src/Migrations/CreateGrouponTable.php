<?php
 
namespace ToDoList\Migrations;
 
use Groupon\Models\GrouponModel;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;
 

class CreateGrouponTable
{

    public function run(Migrate $migrate)
    {
        $migrate->createTable(GrouponModel::class);
    }
}