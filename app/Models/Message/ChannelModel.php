<?php
/**
 * Message渠道模型
 *
 * 2017-01-06
 * @author xuli
 */
namespace App\Models\Message;

use App\Base\BaseModel;

class ChannelModel extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'message_channels';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'alias',
        'is_active',
        'api_type',
    ];

    public $searchFields = ['name', 'alias', 'api_type'];

    public $rules = [
        'create' => [
            'name' => 'required|unique:message_channels,name',
            'api_type' => 'required',
        ],
        'update' => [
            'name' => 'required|unique:message_channels,name,{id}',
            'api_type' => 'required',
        ]
    ];

    public function accounts()
    {
        return $this->hasMany('App\Models\Message\AccountModel', 'channel_id');
    }
}
