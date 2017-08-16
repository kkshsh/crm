<?php
/**
 * Message账号模型
 *
 * 2016-01-11
 * @author Vincent<nyewon@gmail.com>
 */
namespace App\Models\Message;

use App\Base\BaseModel;

class AccountModel extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'message_accounts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account',
        'name',
        'secret',
        'token',
        'channel_id',
        'is_active',

        'aliexpress_member_id',
        'aliexpress_appkey',
        'aliexpress_appsecret',
        'aliexpress_returnurl',
        'aliexpress_refresh_token',
        'aliexpress_access_token',
        'aliexpress_access_token_date',
        'ebay_developer_account',
        'ebay_developer_devid',
        'ebay_developer_appid',
        'ebay_developer_certid',
        'ebay_token',
        'ebay_eub_developer',

    ];

    public $rules = [
        'create' => [
            'account' => 'required|unique:message_accounts,account',
            'name' => 'required|unique:message_accounts,name',
        ],
        'update' => [
            'account' => 'required|unique:message_accounts,account,{id}',
            'name' => 'required|unique:message_accounts,name,{id}',
        ]
    ];

    /**
     * 更多搜索
     * @return array
     */
    public function getMixedSearchAttribute()
    {
        return [
            'relatedSearchFields' => [
            ],
            'filterFields' => [
                'account',
                'name',
            ],
            'filterSelects' => [
                'channel_id' => ChannelModel::all()->pluck('name', 'id'),
            ],
            'selectRelatedSearchs' => [
            ],
            //'sectionSelect' => ['time'=>['created_at']],
        ];
    }

    public function labels()
    {
        return $this->hasMany('App\Models\Message\LabelModel', 'account_id');
    }

    public function accountLabels()
    {
        return $this->hasMany('App\Models\AccountLabelModel', 'account_id');
    }

    public function messages()
    {
        return $this->hasMany('App\Models\MessageModel', 'account_id');
    }

    public function replies()
    {
        return $this->hasManyThrough('App\Models\Message\ReplyModel', 'App\Models\MessageModel',
            'account_id', 'message_id');
    }
    public function foremail()
    {
        return $this->hasManyThrough('App\Models\Message\ForemailModel', 'App\Models\MessageModel',
            'account_id', 'message_id');
    }

    public function channel()
    {
        return $this->belongsTo('App\Models\Message\ChannelModel', 'channel_id');
    }

    public function getApiConfigAttribute()
    {
        $config = [];
        switch($this->channel->api_type) {
            case 'amazon':
                $config =[
                    'serviceUrl' => $this->amazon_api_url,
                    'MarketplaceId.Id.1' => $this->amazon_marketplace_id,
                    'SellerId' => $this->amazon_seller_id,
                    'AWSAccessKeyId' => $this->amazon_accesskey_id,
                    'AWS_SECRET_ACCESS_KEY' => $this->amazon_accesskey_secret,
                    'GmailSecret' => $this->secret,
                    'GmailToken' => $this->token,
                    'account_id' => $this->id,
                    'account_email' => $this->account,
                ];
                break;
            case 'aliexpress':
                $config = [
                    'appkey' => $this->aliexpress_appkey,
                    'appsecret' => $this->aliexpress_appsecret,
                    'returnurl' => $this->aliexpress_returnurl,
                    'access_token_date' => $this->aliexpress_access_token_date,
                    'refresh_token' => $this->aliexpress_refresh_token,
                    'access_token' => $this->aliexpress_access_token,
                    'aliexpress_member_id' => $this->aliexpress_member_id,
                    'operator_id' => $this->operator_id,
                    'customer_service_id' => $this->customer_service_id,
                ];
                break;
            case 'ebay':
                $config = [
                    'requestToken' => $this->ebay_token,
                    'devID'        => $this->ebay_developer_devid,
                    'appID'        => $this->ebay_developer_appid,
                    'certID'       => $this->ebay_developer_certid,
                    'accountName'    => $this->account,
                    'accountID'    => $this->id
                ];
                break;

        }
        return $config;
    }

    public function accounts_labels()
    {
        return $this->hasMany('App\Models\AccountLabelModel', 'account_id');
    }

    public function getIsGetMailAttribute()
    {
        return $this->accounts_labels()->where('is_get_mail', 'get')->get();
    }

}
