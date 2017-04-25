<?php
namespace GroupON\Models;
 
use Plenty\Modules\Plugin\DataBase\Contracts\Model;
 
/**
 * Class GroupON
 *
 * @property int     $id
 * @property string  $taskDescription
 * @property int     $userId
 * @property boolean $isDone
 * @property string  $createdAt
 */
class GroupON extends Model
{
    /**
     * @var int
     */
    public $id              = 0;
    public $orderID = '';
    public $externalOrderID          = '';

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'GroupON::GroupON';
    }
}