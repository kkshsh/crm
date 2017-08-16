<?php
/**
 * Created by PhpStorm.
 * User: Norton
 * Date: 2016/9/14
 * Time: 15:31
 */
namespace App\Models\Message\Issues;
use App\Base\BaseModel;

class AliexpressIssueListModel extends BaseModel
{
    protected $table = 'aliexpress_issues_list';
    public $rules = [];
    public $searchFields =[];
    protected $guarded = [];

    public function account(){
        return $this->hasOne('App\Models\Channel\AccountModel','id','account_id');

    }
    public function getIssueTypeNameAttribute(){
        if($this->issueType){
            return config('message.aliexpress.issueType')[$this->issueType];
        }else{
            return '';
        }
    }
    public function getaccountNameAttribute(){
        if($account = $this->account){
            return  $account->account ? $account->account : '';
        }else{
            return '';
        }
    }

}
