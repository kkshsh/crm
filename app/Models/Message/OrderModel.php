<?php
/**
 * Message订单模型
 *
 * 2016-01-12
 * @author Vincent<nyewon@gmail.com>
 */
namespace App\Models\Message;

use App\Base\BaseModel;

class OrderModel extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'message_orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'message_id',
        'order_id',
        'created_at',
    ];

    public function message()
    {
        return $this->belongsTo('App\Models\MessageModel', 'message_id');
    }

    public function order()
    {
        return $this->hasOne('App\Models\OrderModel', 'id', 'order_id');
    }

}
