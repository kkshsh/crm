<?php
/**
 * Message内容模型
 *
 * 2016-01-12
 * @author Vincent<nyewon@gmail.com>
 */
namespace App\Models\Message;

use App\Base\BaseModel;

class PartModel extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'message_parts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'message_id',
        'part_id',
        'mime_type',
        'filename',
        'headers',
        'body',
    ];

    public function message()
    {
        return $this->belongsTo('App\Models\MessageModel', 'message_id');
    }

}
