<?php
/**
 * Item模型
 *
 * 2016-01-13
 * @author Vincent<nyewon@gmail.com>
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemModel extends Model
{

    protected $connection = 'workstation';
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'item';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

}
