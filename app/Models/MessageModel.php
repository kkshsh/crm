<?php
/**
 * Message模型
 *
 * 2016-01-12
 * @author Vincent<nyewon@gmail.com>
 */
namespace App\Models;

use App\Base\BaseModel;
use App\Models\Order\PackageModel;
use App\Models\Message\ChannelModel;
use App\Models\Message\AccountModel;
use Tool;
use App\Models\UserAccountModel;

class MessageModel extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account_id',
        'assign_id',
        'message_id',
        'mime_type',
        'from',
        'from_name',
        'to',
        'date',
        'subject',
        'start_at',
        'content',
        'channel_order_number',
        'title_email',
        'required',
    ];

    public $searchFields = ['id','subject', 'from_name', 'from', 'to','content', 'channel_order_number'];

    public $appends = ['channel_name', 'account_name'];

    /**
     * 更多搜索
     * @return array
     */
    public function getMixedSearchAttribute()
    {
        return [
            'relatedSearchFields' => [],
            'filterFields' => [
                'from_name',
                'from',
                'label'
            ],
            'filterSelects' => [
                'status' => config('message.statusText'),
                'assign_id' => UserModel::all()->pluck('name','id'),
                'channel_id' => ChannelModel::all()->pluck('name', 'id'),
                'account_id' => AccountModel::all()->pluck('name', 'id'),
            ],
            'selectRelatedSearchs' => [
            ],
            //'sectionSelect' => ['time'=>['created_at']],
        ];
    }

    public $rules = [];

    public function account()
    {
        return $this->belongsTo('App\Models\Message\AccountModel');
    }

    public function assigner()
    {
        return $this->belongsTo('App\Models\UserModel', 'assign_id');
    }

    public function parts()
    {
        return $this->hasMany('App\Models\Message\PartModel', 'message_id');
    }

    public function attachments()
    {
        return $this->hasMany('App\Models\Message\MessageAttachment', 'message_id');
    }


    public function replies()
    {
        return $this->hasMany('App\Models\Message\ReplyModel', 'message_id');
    }
    public function foremail()
    {
        return $this->hasMany('App\Models\Message\ForemailModel', 'message_id');
    }

    public function relatedOrders()
    {
        return $this->hasMany('App\Models\Message\OrderModel', 'message_id');
    }

    //关联complaint表
    public function relatedCom()
    {
        return $this->hasMany('App\Models\Message\MessageComplaintModel', 'message_id');
    }

    //关联complaint表
    public function relatedMessage()
    {
        return $this->hasMany('App\Models\Message\MessageOrderModel', 'message_id');
    }

    public function channel(){
        return $this->belongsTo('App\Models\Message\ChannelModel', 'channel_id', 'id');
    }

    public function getAssignerNameAttribute()
    {
        if ((!empty($this->assign_id)) && isset($this->assigner->name))
        {
            return $this->assigner->name;
        }else{
            return '未知';
        }
    }

    public function getChannelNameAttribute()
    {
        $channel = $this->channel;
        return ! empty($channel) ? $channel->name : '未知';
    }

    public function getLabelTextAttribute()
    {
        switch ($this->label) {
            case 'INBOX':
                $result = "<span class='label label-success'>INBOX</span>";
                break;
            case 'SPAM':
                $result = "<span class='label label-warning'>SPAM</span>";
                break;
            case 'TRASH':
                $result = "<span class='label label-danger'>TRASH</span>";
                break;
            default:
                $result = "<span class='label label-info'>$this->label</span>";
                break;
        }
        return $result;
    }

    public function getStatusTextAttribute()
    {
        return config('message.statusText.' . $this->status);
    }

    public function getMessageContentAttribute()
    {
        $plainBody = '';
        $attachments = $this->attachments;
        if ($this->channel_name == 'Choies')
            $attachments = $this->parts;
        foreach ($attachments as $part) {
            if ($part->mime_type == 'text/html') {
                $htmlBody = Tool::base64Decode($part->body);
                $htmlBody=preg_replace("/<(\/?body.*?)>/si","",$htmlBody);
            }
            if ($part->mime_type == 'text/plain') {
                $plainBody .= nl2br(Tool::base64Decode($part->body));
            }
        }
        $body = isset($htmlBody) && $htmlBody != '' ? $htmlBody : $plainBody;
        return $body;
    }


    public function getMessageAttanchmentsAttribute()
    {
        $attanchments = [];
        $attachments = $this->attachments;
//        if ($this->channel_name == 'Choies')
//            $attachments = $this->parts;
        foreach ($attachments as $key => $part) {
            if ($part->filename) {
                $attanchments[$key]['filename'] = $part->filename;
//                if ($this->channel_name != 'Choies') {
                $attanchments[$key]['filepath'] = '/' . config('message.attachmentSrcPath') . $part->gmail_message_id . '/' . $part->filename;
//                } else {
//                    $attanchments[$key]['filepath'] = '/' . config('message.attachmentSrcPath') . $part->id . '_' . $part->filename;
//                }
            }
        }
        return $attanchments;
    }

    public function getHistoriesAttribute()
    {
        return MessageModel::where('from','=', $this->from)
            ->where('id', '<>', $this->id)
            ->where('from', '<>', '')
            ->orderBy('created_at', 'desc')
            ->take(1)
            ->get();
    }

    public function getLastAttribute()
    {
        if($this->histories){
            if($this->histories->last()){
                if($this->histories->last()->assign_id==14 || $this->histories->last()->assign_id==17){
                    $data1['assign_id']="10";
                    $old_id=$this->histories->last()->id;
                    $k=MessageModel::find($old_id);
                    $k->update($data1);
                }
            }
            return $this->histories->last();

        }
    }

    public function getDelayAttribute()
    {
        return ceil((time() - strtotime($this->date)) / 60);
    }

    public function assign($userId)
    {
        switch ($this->status) {
            case 'UNREAD':
                $this->assign_id = $userId;
                $this->status = 'PROCESS';
                return $this->save();
                break;
            default:
                return $this->assign_id == $userId;
                break;
        }
    }

    public function guessRelatedOrders($email = null)
    {
        $relatedOrders = [];
        if ($this->last) {
//            $relatedOrders['history'] = $this->last->relatedOrders;
        }
        $email = $email ? $email : $this->from;
//        $relatedOrders['email'] = OrderModel::where('email', $email)->get();
        // CRM对接ERP订单,旧WS订单不关联（根据API-crm_get_ordernum）
        $api_type = $this->channel->api_type;
        if ($api_type == 'amazon') {
            # 亚马逊
            $relatedOrders['email'] = $this->getOrdersByEmail_erp($email);
        }else if ($api_type == 'ebay') {
            # ebay
            $relatedOrders['email'] = $this->getOrdersByCustomerId_erp($email);
        }else if ($api_type == 'aliexpress') {
            # 速卖通
            if ($this->label == "站内信"){
                $relatedOrders['smtemail'] = $this->getOrdersByCustomerId_erp($this->from);
            } else {
                $relatedOrders['email'] = $this->getOrdersByOrdernum_erp($this->channel_order_number);
            }
        }
        return $relatedOrders;
    }

    public function getOne($userId)
    {
        $account_ids = UserAccountModel::where('user_id', $userId)->get()->pluck('account_id');
        return $this
            ->where('status', 'UNREAD')
            ->whereIn('account_id', $account_ids)
            ->orWhere(function ($query) use ($userId) {
                $query->where('assign_id', $userId)
                    ->where('status', 'PROCESS')
                    ->where('dont_reply','<>',1);
            })->first();
    }

    public function setRelatedOrders($numbers)
    {
        if ($numbers) {
            foreach ($numbers as $number) {
//                $order = OrderModel::ofOrdernum($number)->first();
//                if ($order) {
//                    $this->relatedOrders()->create(['order_id' => $order->id]);
//                } else {
//                    $package = PackageModel::ofTrackingNo($number)->first();
//                    if ($package) {
//                        $this->relatedOrders()->create(['order_id' => $package->order_id]);
//                    }
//                }
                // CRM对接ERP,查询ERP有没有该订单号
                $order_erp =  $this->getOrderById_erp($number);
                if (count($order_erp)>0 and isset($order_erp[0]) and isset($order_erp[0]['id'])) {
                    $order_id = $order_erp[0]['id'];
					$erpData = config('setting.erpData');
                    $old_ws_data = date('Y-m-d',strtotime("$erpData  +1 day"));
                    // CRM对接ERP,旧WS关联的订单，create_at存2016-09-02，这样区分新旧ERP的order_id
                    $this->relatedOrders()->create(['order_id' => $order_id , 'created_at' => $old_ws_data]);
                }
            }
            if ($this->relatedOrders()->count() > 0) {
                $this->related = 1;
                $this->start_at = date('Y-m-d H:i:s', time());
                return $this->save();
            }
        }
        return false;
    }

    public function cancelRelatedOrder($relatedOrderId)
    {
        $relatedOrder = $this->relatedOrders()->find($relatedOrderId);
        if ($relatedOrder) {
            $relatedOrder->delete();
            if ($this->relatedOrders()->count() < 1) {
                $this->related = 0;
                $this->save();
            }
            return true;
        }
        return false;
    }

    public function notRelatedOrder()
    {
        $this->related = 1;
        return $this->save();
    }

    public function assignToOther($fromId, $assignId)
    {

        if ($this->assign_id == $fromId) {
            $assignUser = UserModel::find($assignId);
            if ($assignUser) {
                $this->assign_id = $assignId;
                return $this->save();
            }
        }
        return false;
    }

    public function notRequireReply($userId)
    {
        if ($this->assign_id == $userId) {
            $this->required = 0;
            $this->status = 'COMPLETE';
            return $this->save();
        }
        return false;
    }

    public function reply($data)
    {
        $data['to_email'] = trim($data['to_email']);
        if ($this->replies()->create($data)) {
            //记录回复邮件类型
            $this->type_id = $data['type_id']?$data['type_id']:"";
            $this->status = 'COMPLETE';
            $this->end_at = date('Y-m-d H:i:s', time());
            return $this->save();
        }
        return false;
    }

    public function getOne1($userId,$id)
    {
        $id=intval($id);
        return $this
            ->where('assign_id', $userId)
            ->where('status', 'PROCESS')
            ->where('id','<>',$id)
            ->where('dont_reply','<>',1)
            ->orWhere(function ($query) use ($userId) {
                $query->where('label','INBOX')->where('status', 'UNREAD');
            })->first();
    }



    public function dontRequireReply($userId)
    {
        if ($this->assign_id == $userId) {
            $this->required = 0;
            $this->status = 'PROCESS';
            $this->dont_reply = 1;
            return $this->save();
        }
        return false;
    }

    public function getOrdersByEmail_erp($email)
    {
        $post_data=array();
        $post_data['email'] = $email;
        $url = "http://erp.wxzeshang.com:8000/api/crm_get_ordernum/";
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT,20);
        curl_setopt ( $ch, CURLOPT_TIMEOUT,20);
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_POST, true );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_data);
        $json_order_info = curl_exec ( $ch );
        if( curl_errno ( $ch )) {
            echo  'Curl error: '  .  curl_error ( $ch );
            return;
        }
        curl_close($ch);

        $order_info['order_info'] = array();
        if ($json_order_info!=false)
        {
            $order_info_arr = json_decode($json_order_info,true);
            $order_info['order_info'] = $order_info_arr['order_info'];
        }
        return $order_info['order_info'];
    }

    # 速卖通根据message表的channel_order_number获取erp系统的订单信息
    public function getOrdersByOrdernum_erp($channel_order_num)
    {
        $post_data=array();
        $post_data['ordernum'] = $channel_order_num;
        $url = "http://erp.wxzeshang.com:8000/api/crm_get_order/";
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT,20);
        curl_setopt ( $ch, CURLOPT_TIMEOUT,20);
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_POST, true );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_data);
        $json_order_info = curl_exec ( $ch );
        if( curl_errno ( $ch )) {
            echo  'Curl error: '  .  curl_error ( $ch );
            return;
        }
        curl_close($ch);

        $order_info['order_info'] = array();
        if ($json_order_info!=false)
        {
            $order_info_arr = json_decode($json_order_info,true);
            $order_info['order_info'] = $order_info_arr['order_info'];
        }
        return $order_info['order_info'];
    }

    # Ebay根据message表的from(存客户ID)获取erp系统的订单信息
    public function getOrdersByCustomerId_erp($custom_id)
    {
        $post_data=array();
        $post_data['custom_id'] = $custom_id;
        $url = "http://erp.wxzeshang.com:8000/api/crm_get_order_by_custom_id/";
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT,20);
        curl_setopt ( $ch, CURLOPT_TIMEOUT,20);
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_POST, true );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_data);
        $json_order_info = curl_exec ( $ch );
        if( curl_errno ( $ch )) {
            echo  'Curl error: '  .  curl_error ( $ch );
            return;
        }
        curl_close($ch);

        $order_info['order_info'] = array();
        if ($json_order_info!=false)
        {
            $order_info_arr = json_decode($json_order_info,true);
            $order_info['order_info'] = $order_info_arr['order_info'];
        }
        return $order_info['order_info'];
    }

    public function getOrderById_erp($order_id)
    {
        $post_data=array();
        $post_data['order_id'] = $order_id;
        $url = "http://erp.wxzeshang.com:8000/api/crm_get_order_info/";
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT,20);
        curl_setopt ( $ch, CURLOPT_TIMEOUT,20);
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_POST, true );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_data);
        $json_order_info = curl_exec ( $ch );
        if( curl_errno ( $ch )) {
            echo  'Curl error: '  .  curl_error ( $ch );
            return;
        }
        curl_close($ch);

        $result = array();
        if ($json_order_info!=false)
        {
            $result[] = json_decode($json_order_info,true);
        }
        return $result;
    }
    public function getAccountNameAttribute(){
        $account = $this->account;
        return empty($account) ? '' : $account->name;
    }

    public function getContentDecodeBase64Attribute(){
        if($this->content){
            return unserialize(base64_decode($this->content));
        }else{
            return '';
        }
    }

    public function getMessageInfoAttribute(){
        $html = '';
        foreach($this->ContentDecodeBase64 as $key => $content){
            switch ($key){
                case 'wish':
                    foreach ($content as $k => $item){
                        if(!empty($item['Reply']['message'])){
                            if($item['Reply']['sender'] != 'merchant'){
                                if($item['Reply']['sender'] == 'wish support'){
                                    $this->from_name = $item['Reply']['sender'];
                                }
                                $html .= '<div class="alert alert-warning col-md-10" role="alert"><p><strong>发件人：</strong>'.$this->from_name.':</p><strong>内容: </strong>'.$item['Reply']['message'];
                                $html .= '<p class="time"><strong>时间：</strong>'.$item['Reply']['date'].'</p>';

                                if(isset($item['Reply']['translated_message']) && isset($item['Reply']['translated_message_zh'])){
                                    $html .= '<div class="alert-danger"><strong>Wish翻译: </strong><p>'.$item['Reply']['translated_message'].'</p><p>'. $item['Reply']['translated_message_zh'].'</p></div>';
                                }else{

                                }
                                if(! empty($item['Reply']['image_urls'])){
                                    $img_urls = $item['Reply']['image_urls'];
                                    $img_urls = str_replace('[', '', $img_urls);
                                    $img_urls = str_replace(']', '', $img_urls);
                                    $img_urls = explode(',', $img_urls);
                                    foreach($img_urls as $url){
                                        $tmp_url = explode('\'', $url);
                                        if(! empty($tmp_url[1])){
                                            $html .= '附图：<img width="500px" src="'.$tmp_url[1].'" /> <br/>';
                                        }
                                    }
                                }
                                $html .= '</div>';
                            }else{
                                $html .= '<div class="alert alert-success col-md-10" role="alert" style="float: right"><p><strong>发件人：</strong>'.$item['Reply']['sender'].':</p><strong>内容: </strong>'.$item['Reply']['message'];
                                $html .= '<p class="time"><strong>时间：</strong>'.$item['Reply']['date'].'</p>';
                                $html .= '</div>';
                            }


                        }
                    }
                    break;
                case 'aliexpress':
                    if (!isset($content))
                        continue;
                    $message_content = array_reverse($content->result); //逆序
//dd($message_content);
                    $product_html = '';
                    $message_fields_ary = false;
                    foreach ($message_content as $k => $item){

                        if($k==0 && ! empty($item->summary->orderUrl)){
                            $product_html .= '<div class="col-lg-12" >渠道订单链接:<a target="_blank" href="' . $item->summary->orderUrl . '">'.$item->summary->orderUrl.'</a></div>';
                        }

                        //dd($message_fields_ary);
                        $row_html = '';
                        if($item->content == '< img >'){
                            foreach ($item->filePath as $item_path){
                                if($item_path->mPath){
                                    $row_html .='<img src="'.$item_path->mPath.'" /><a href="'.$item_path->lPath.'" target="_blank">查看大图</a>';
                                }
                            }
                        }
                        $content = $item->content;
                        $content = str_replace("&nbsp;", ' ', $content);
                        $content = str_replace("&amp;nbsp;", ' ', $content);
                        $content = str_replace("&amp;iquest;", ' ', $content);
                        $content = str_replace("\n", "<br />", $content);
                        $content = preg_replace("'<br \/>[\t]*?<br \/>'", '', $content);
                        $content = preg_replace("'\/\:0+([0-9]+0*)'", "<img style='width:35px' src='http://i02.i.aliimg.com/wimg/feedback/emotions/\\1.gif' />", $content);
                        $content = (stripslashes(stripslashes($content)));

                        $datetime = date('Y-m-d H:i:s',$item->gmtCreate/1000);
                        if($this->from_name != $item->summary->receiverName){
                            if($row_html != ''){
                                $html .= '<div class="alert alert-warning col-md-10" role="alert"><p><strong>Sender: </strong>'.$item->senderName.':</p><strong>Content: </strong>'.$row_html;
                                $html .= '<p class="time"><strong>Time: </strong>'.$datetime.'</p>';
                                $html .= '</div>';
                            }else{
                                $html .= '<div class="alert alert-warning col-md-10" role="alert"><p><strong>Sender: </strong>'.$item->senderName.':</p><strong>Content: </strong>'.$content;
                                $html .= '<p class="time"><strong>Time: </strong>'.$datetime.'</p>';
                                $html .= '<button style="float: right;" type="button" class="btn btn-success btn-translation" need-translation-content="'.preg_replace("'\/\:0+([0-9]+0*)'", '',$content).'" content-key="'.$k.'">
                                翻译
                            </button>
                            <p id="content-'.$k.'" style="color:green"></p>';
                                $html .= '</div>';
                            }
//echo $content.'----';  /:011   /:011 /:000
                        }else{
                            $html .= '<div class="alert alert-success col-md-10" role="alert" style="float: right"><p><strong>Sender: </strong>'.$item->senderName.':</p><strong>Content: </strong>'.$content;
                            $html .= '<p class="time"><strong>Time: </strong> '.$datetime.'</p>';
                            $html .= '</div>';
                        }

                        # 如果有图片则显示图片
                        if(isset($item->summary->productImageUrl))
                        {
                            $img_html = '';
                            $html .= $this->createAliexpressImgDiv($message_fields_ary, $item, $img_html);
                        }
                    }
                    break;

                case 'ebay':
                    $html = $content;
                    break;
                case 'amazon':
                    $html = $content;
                    break;
                default :
                    $html = 'invaild channel message';
            }
        }
        return empty($product_html) ? $html : $product_html.$html;
    }

    public function createAliexpressImgDiv($message_fields_ary, $item, $img_html){
        if($item->messageType == 'product'){
            $message_fields_ary['product_img_url']      = isset($item->summary->productImageUrl) ? $item->summary->productImageUrl : '';
            $message_fields_ary['product_product_url']  = isset($item->summary->productDetailUrl) ? $item->summary->productDetailUrl : '';
            $message_fields_ary['product_product_name'] = isset($item->summary->productName) ? $item->summary->productName : '';

            $img_html .= '<div class="col-lg-12 alert-default">';
            $img_html .= '<table class="table table-bordered table-striped table-hover sortable">';
            $img_html .= '<tr>';
            $img_html .= '<th>产品图片</th>';
            $img_html .= '<th>产品名称</th>';
            $img_html .= '<th>产品连接</th>';
            $img_html .= '</tr>';
            $img_html .= '<tr>';
            $img_html .= '<td><img src ="'.$message_fields_ary['product_img_url'] .'"/></td>';
            $img_html .= '<td>'.$message_fields_ary['product_product_name'] .'</td>';
            $img_html .= '<td><a target="_blank" href="'.$message_fields_ary['product_product_url'].'">链接</a></td>';
            $img_html .= '</tr>';
            $img_html .= '</table>';
            $img_html .= '</div>';
            return $img_html;
        }
    }

}
