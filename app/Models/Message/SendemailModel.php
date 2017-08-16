<?php
/**
 * 转发邮件模型
 *
 * 2016-01-18
 * @author Vincent<nyewon@gmail.com>
 */
namespace App\Models\Message;

use App\Base\BaseModel;

class SendemailModel extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'message_sendemail';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'message_id',
        'to',
        'to_email',
        'title',
        'content',
        'status',
        'updatefile',
    ];

    public $rules = [
        'create' => [
            'to' => 'required',
            'to_email' => 'required',
            'title' => 'required',
            'content' => 'required',
            'updatefile'=>'required',
        ],
        'update' => [
            'title' => 'required',
            'content' => 'required',
        ]
    ];
    public $searchFields = ['id','message_id', 'to', 'to_email','title'];
    public function message()
    {
        return $this->belongsTo('App\Models\MessageModel', 'message_id');
    }

}
