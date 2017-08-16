<?php

namespace App\Models;

use App\Base\BaseModel;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use App\Models\Message\Template\TypeModel;
use App\Models\Message\Message_logModel;
use App\Models\Message\ForemailModel;

class UserModel extends BaseModel implements AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['parent_id', 'group', 'name', 'email', 'password','is_login'];

    public $searchFields = ['subject', 'from_name', 'from', 'to'];

    public $rules = [
        'create' => [
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'password' => 'required',
            'is_login' => 'required',
        ],
        'update' => [
            'name' => 'required',
            'email' => 'required|unique:users,email,{id}',
            'password' => 'required',
            'is_login' => 'required',
        ]
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    public function parent()
    {
        return $this->belongsTo('App\Models\UserModel', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany('App\Models\UserModel', 'parent_id');
    }

    public function messages()
    {
        return $this->hasMany('App\Models\MessageModel', 'assign_id');
    }

    public function getProcessMessagesAttribute()
    {
        return $this->messages()->where('status', 'PROCESS')->count();
    }

    public function getGroupTextAttribute()
    {
        return config('user.group.' . $this->group);
    }
    //获取回复邮件成功的总数
    public function getProcessMessAttribute($where)
    {
        return $this->messages()->where('status', 'COMPLETE')->where('required','>',0)->count();
    }

    //获取邮件分类的总数
    public function ProcessType($id)
    {
        return $this->messages()->where('status', 'COMPLETE')->where('type_id',$id)->count();
    }

    //获取其他邮件类型的总数
    public function getProcessOrderAttribute()
    {
        return $this->messages()->where('status', 'COMPLETE')->where('required','>',0)->where('type_id',0)->count();
    }

    //获取转交邮件的总数
    public function MessageFor($foruser)
    {
        return Message_logModel::where('foruser', $foruser)->count();
    }

    //获取转发邮件的总数
    public function MessageLog($id)
    {
        return ForemailModel::where('assign_id', $id)->count();
    }

    public function getmessage($start_time,$end_time)
    {
        return $this->messages()->where('status', 'COMPLETE')->where('updated_at','>',$start_time)->where('updated_at','<=',$end_time)->count();
    }

    public function getmessage1($id,$start_time,$end_time)
    {
        return $this->messages()->where('status', 'COMPLETE')->where('type_id',$id)->where('updated_at','>',$start_time)->where('updated_at','<=',$end_time)->count();
    }

    public function getmessage2($start_time,$end_time)
    {
        return $this->messages()->where('status', 'COMPLETE')->where('required',1)->where('type_id',0)->where('updated_at','>',$start_time)->where('updated_at','<=',$end_time)->count();
    }

    public function getmessage3($foruser,$start_time,$end_time)
    {
        return Message_logModel::where('foruser', $foruser)->where('updated_at','>',$start_time)->where('updated_at','<=',$end_time)->count();
    }

    public function getmessage4($foruser,$start_time,$end_time)
    {
        return ForemailModel::where('assign_id', $foruser)->where('updated_at','>',$start_time)->where('updated_at','<=',$end_time)->count();
    }

    public function accounts()
    {
        return $this->hasMany('App\Models\UserAccountModel',"user_id","id");
    }
}
