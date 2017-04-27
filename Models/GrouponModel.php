<?php
 
namespace Groupon\Models;
 
use Plenty\Modules\Plugin\DataBase\Contracts\Model;
 
class GrouponModel extends Model
{
    public $createdAt = '';
 
  
    public function getTableName(): string
    {
        return 'Groupon::GrouponModel';
    }
}