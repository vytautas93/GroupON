<?php
 
namespace Groupon\Models;
 
use Plenty\Modules\Plugin\DataBase\Contracts\Model;
 
 
/**
 * Class Expire
 *
 * @property int     $id
 * @property int     $plentyID
 * @property string  $expiredtime
 */ 
 
class Expire extends Model
{
    public $id = 0;
    public $expiredtime = 0;

    public function getTableName(): string
    {
        return 'Groupon::Expire';
    }
}