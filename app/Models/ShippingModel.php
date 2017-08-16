<?php
/**
 * 物流模型
 *
 * 2016-01-13
 * @author Vincent<nyewon@gmail.com>
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingModel extends Model
{

    protected $connection = 'workstation';
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'shipping';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

}
