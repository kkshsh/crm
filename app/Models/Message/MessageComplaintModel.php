<?php
/**
 * 转发邮件模型
 *
 * 2016-01-18
 * @author Vincent<nyewon@gmail.com>
 */
namespace App\Models\Message;

use App\Base\BaseModel;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
//use App\Models\Message\MessageOrderModel;

class MessageComplaintModel extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'message_complaint';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'message_id',
        'ordernum',
        'email',
		'settled_name',
		'refund',
        'ws_return',
        'created',
    ];

    public $rules = [
        'create' => [
            'message_id' => 'required',
            'ordernum' => 'required',
            'email' => 'required',
            'created' => 'required',
        ],
    ];
    public $searchFields = ['id','message_id', 'ordernum', 'email'];

    public function assigner1()
    {
        return $this->hasMany('App\Models\Message\MessageOrderModel',"com_id","id");
    }



}
