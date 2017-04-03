<?php
 
namespace GroupON\Models;
 
use Plenty\Modules\Plugin\DataBase\Contracts\Model;
 
/**
 * Class GroupON
 *
 * @property int     $id
 * @property string  $supplierID
 * @property string  $token
 * @property string  $createdAt
 */
class GroupOn extends Model
{
    /**
     * @var int
     */
    public $id              = 0;
    public $supplierID = '';
    public $token          = '';
    public $createdAt       = '';
 
    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'GroupON::GroupOn';
    }
}