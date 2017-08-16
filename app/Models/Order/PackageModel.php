<?php
/**
 * 订单包裹模型
 *
 * 2016-01-13
 * @author Vincent<nyewon@gmail.com>
 */
namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;

class PackageModel extends Model
{
    protected $connection = 'workstation';
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'package';

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

    public function shipping()
    {
        return $this->belongsTo('App\Models\ShippingModel', 'shipping_id');
    }

    public function items()
    {
        return $this->hasMany('App\Models\Order\Package\ItemModel', 'package_id');
    }

    public function getStatusTextAttribute()
    {
        return config('order.package.status.' . $this->status);
    }

    public function scopeOfTrackingNo($query, $trackingNo)
    {
        return $query->where('tracking_no', $trackingNo);
    }

}
