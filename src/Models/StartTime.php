<?php
 
namespace Groupon\Models;
 
use Plenty\Modules\Plugin\DataBase\Contracts\Model;
 
 
/**
 * Class StartTime
 *
 * @property int     $id
 * @property string  $startTime
 * @property string  $createdAt
 */ 
 
class StartTime extends Model
{
    public $id = 0;
    public $startTime = '';
    public $createdAt = '';
 
  
    public function getTableName(): string
    {
        return 'Groupon::StartTime';
    }
}