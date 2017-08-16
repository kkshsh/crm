<?php
/**
 * 账号对应的标签（账号和标签是1：N的关系）
 *
 * 2017-01-16
 * @author xuli
 */
namespace App\Models;

use App\Base\BaseModel;

class AccountLabelModel extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'accounts_labels';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account_id',
        'label_id',
        'name',
        'is_get_mail',
    ];

    public function account_name()
    {
        return $this->belongsTo('App\Models\Message\AccountModel', 'account_id');
    }

}
