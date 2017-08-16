<?php
/**
 * User: Norton
 * Date: 2016/8/13
 * Time: 15:31
 */
namespace App\Models\Message\Issues;
use App\Base\BaseModel;
use App\Models\OrderModel;
use App\Models\Order\ItemModel;
class EbayCasesListsModel extends BaseModel{
    protected $table = 'ebay_cases_lists';
    public $rules = [];
    public $searchFields =['id' => 'ID' , 'buyer_id' => '买家ID' , 'seller_id' => '卖家ID' ,'transaction_id' => '交易号'];
    protected $guarded = [];

    public function account()
    {
        return $this->hasOne('App\Models\Channel\AccountModel', 'id', 'account_id');
    }
    public function orderItem(){
        return $this->hasOne('App\Models\Order\ItemModel','transaction_id','transaction_id');
    }
/*    public function order(){
        return $this->belongsTo('App\Models\OrderModel','','');
    }*/

    public function getCaseContentAttribute(){
        $html = '';
        $note = unserialize(base64_decode($this->content));

        //dd($note);

        if(is_array($note)){
            if(isset($note['role'])){ //单条
                if($note['role'] == 'BUYER'){
                    $html .= '<div class="alert alert-warning col-md-10" role="alert">';
                    $html .= '<p>buyer:'.$this->buyer_id.'</p>';
                    $html .= '<p>seller:'.$this->seller_id.'</p>';
                    $html .= '<p>状态:'.$this->seller_id.'</p>';
                    $html .= '<p>activity:'.$this->seller_id.'</p>';
                    $html .= '<p>Date:'.$note['creationDate'].'</p>';
                    $html .= '<p>Date:'.$note['note'].'</p>';
                    $html .= '</div>';
                }else{
                    $html .= '<div class="alert alert-success col-md-10" role="alert" style="float: right">';
                    $html .= '<p>buyer:'.$this->buyer_id.'</p>';
                    $html .= '<p>seller:'.$this->seller_id.'</p>';
                    $html .= '<p>状态:'.$this->seller_id.'</p>';
                    $html .= '<p>activity:'.$this->seller_id.'</p>';
                    $html .= '<p>Date:'.$note['creationDate'].'</p>';
                    $html .= '<p>note:'.$note['note'].'</p>';
                    $html .= '</div>';
                }

            }else{ //多条
                foreach (array_reverse($note) as $item){
                    if($item['role'] == 'BUYER'){
                        $html .= '<div class="alert alert-warning col-md-10" role="alert">';

                        $html .= '<p>Date:'.$item['creationDate'].'</p>';
                        $html .= '<p>Date:'.$item['note'].'</p>';
                        $html .= '</div>';
                    }else{
                        $html .= '<div class="alert alert-success col-md-10" role="alert" style="float: right">';
                        $html .= '<p>activity:'.$this->seller_id.'</p>';
                        $html .= '<p>Date:'.$item['creationDate'].'</p>';
                        $html .= '<p>note:'.$item['note'].'</p>';
                        $html .= '</div>';
                    }
                }
            }




        }


        return $html;
    }

    public function getCaseOrderInfoAttribute()
    {
        if (!empty($this->transaction_id)) {
            $realted_order = ItemModel::where('transaction_id', $this->transaction_id)->first();
            if (!empty($realted_order)) {
                return $realted_order;
            }
        }
        return '';
    }
}


































