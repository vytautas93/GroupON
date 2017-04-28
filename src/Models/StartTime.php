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
    public $plentyID = 0;
    public $expiredtime = '';

 
    public function getTableName(): string
    {
        return 'Groupon::StartTime';
    }
}