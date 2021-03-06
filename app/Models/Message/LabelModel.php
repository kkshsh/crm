<?php
/**
 * Message标签模型
 *
 * 2016-01-11
 * @author Vincent<nyewon@gmail.com>
 */
namespace App\Models\Message;

use App\Base\BaseModel;

class LabelModel extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'message_labels';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account_id',
        'label_id',
        'name',
        'message_list_visibility',
        'label_list_visibility',
        'type',
    ];

    public function account()
    {
        return $this->belongsTo('App\Models\Message\AccountModel', 'account_id');
    }

}
