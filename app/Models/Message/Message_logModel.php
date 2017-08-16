<?php
/**
 * 转发邮件模型
 *
 * 2016-01-18
 * @author Vincent<nyewon@gmail.com>
 */
namespace App\Models\Message;

use App\Base\BaseModel;

class Message_logModel extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'messages_log';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'foruser',
        'message_id',
        'assign_id',
        'touser',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    public $searchFields = ['id','foruser', 'message_id', 'assign_id','touser'];

    public function message()
    {
        return $this->belongsTo('App\Models\MessageModel', 'message_id');
    }

}
