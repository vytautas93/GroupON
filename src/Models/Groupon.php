<?php
 
namespace Groupon\Models;
 
use Plenty\Modules\Plugin\DataBase\Contracts\Model;
 
class Groupon extends Model
{
    public $createdAt = '';
 
  
    public function getTableName(): string
    {
        return 'Groupon::Groupon';
    }
}