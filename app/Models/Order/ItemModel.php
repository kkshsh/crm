<?php
/**
 * 订单产品模型
 *
 * 2016-01-13
 * @author Vincent<nyewon@gmail.com>
 */
namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;

class ItemModel extends Model
{
    protected $connection = 'workstation';
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'orderitem';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    public function order()
    {
        return $this->belongsTo('App\Models\OrderModel', 'order_id');
    }

    public function getStatusTextAttribute()
    {
        return config('order.item.status.' . $this->status);
    }

}
