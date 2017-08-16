<?php
/**
 * 用户负责账号模型（用户和账号多对多关系）
 *
 * 2017-01-06
 * @author xuli
 */
namespace App\Models;

use App\Base\BaseModel;

class UserAccountModel extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users_accounts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'account_id',
    ];

    public $rules = [
        'create' => [
            'user_id' => 'required',
            'account_id' => 'required',
        ],
    ];

    public function account_name()
    {
        return $this->belongsTo('App\Models\Message\AccountModel', 'account_id');
    }

}
