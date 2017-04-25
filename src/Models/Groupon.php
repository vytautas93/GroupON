<?php
namespace GroupON\Models;
 
use Plenty\Modules\Plugin\DataBase\Contracts\Model;

class Groupon extends Model
{
    public $id = 0;
    public $orderID = '';
    public $externalOrderID = '';

   
    public function getTableName(): string
    {
        return 'GroupON::Groupon';
    }
}