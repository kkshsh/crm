<?php
/**
 * 转发邮件模型
 *
 * 2016-01-18
 * @author Vincent<nyewon@gmail.com>
 */
namespace App\Models\Message;

use App\Base\BaseModel;

class MessageOrderModel extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'message_order';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'message_id',
        'assign_id',
        'com_id',
        'ordernum',
        'sku',
        'price',
        'qty',
        'com',
        'com_name',
        'content',
        'packageid',
        'refund_amount',
    ];

    public $rules = [
        'create' => [
            'message_id' => 'required',
            'ordernum' => 'required',
            'sku' => 'required',
            'price' => 'required',
            'qty' => 'required',
            'com' => 'required',
            'com_name' => 'required',
        ],
    ];
    public $searchFields = ['id','message_id', 'ordernum', 'sku','com'];

    public function assigner()
    {
        return $this->belongsTo('App\Models\UserModel', 'assign_id');
    }

    public function assigner1()
    {
        return $this->hasMany('App\Models\Message\MessageComplaintModel',"id","com_id");
    }

    public function assigner2()
    {
        return $this->hasMany('App\Models\OrderModel',"ordernum","ordernum");
    }
}
