<?php
/**
 * Message列表模型
 *
 * 2016-01-11
 * @author Vincent<nyewon@gmail.com>
 */
namespace App\Models\Message;

use App\Base\BaseModel;

class ListModel extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'message_lists';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account_id',
        'next_page_token',
        'result_size_estimate',
    ];

    public function account()
    {
        return $this->belongsTo('App\Models\Message\AccountModel', 'account_id');
    }

}
