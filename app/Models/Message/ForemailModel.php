<?php
/**
 * 转发邮件模型
 *
 * 2016-01-18
 * @author Vincent<nyewon@gmail.com>
 */
namespace App\Models\Message;

use App\Base\BaseModel;

class ForemailModel extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'message_foremail';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'message_id',
        'assign_id',
        'to',
        'to_email',
        'title',
        'content',
        'status',
        'to_useremail',
    ];

    public $rules = [
        'create' => [
            'to' => 'required',
            'to_email' => 'required',
            'title' => 'required',
            'content' => 'required',
        ],
        'update' => [
            'title' => 'required',
            'content' => 'required',
        ]
    ];
    public $searchFields = ['id','to', 'to_email', 'content'];
    public function message()
    {
        return $this->belongsTo('App\Models\MessageModel', 'message_id');
    }

}
