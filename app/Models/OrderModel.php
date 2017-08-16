<?php
/**
 * Order模型
 *
 * 2016-01-13
 * @author Vincent<nyewon@gmail.com>
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderModel extends Model
{

    protected $connection = 'workstation';
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'order';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    public function packages()
    {
        return $this->hasMany('App\Models\Order\PackageModel', 'order_id');
    }

    public function items()
    {
        return $this->hasMany('App\Models\Order\ItemModel', 'order_id');
    }

    public function getStatusTextAttribute()
    {
        return config('order.status.' . $this->status);
    }

    public function getActiveTextAttribute()
    {
        return config('order.active.' . $this->active);
    }

    public function scopeOfOrdernum($query, $ordernum)
    {
        return $query->where('ordernum', $ordernum);
    }
    public function scopeOfOrderemail($query, $orderemail)
    {
        return $query->where('email', $orderemail);
    }
}
