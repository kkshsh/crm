<?php
/**
 * Created by PhpStorm.
 * User: Vincent
 * Date: 16/1/8
 * Time: 上午9:19
 */

namespace App\Http\Controllers;

use App\Models\Message\MessageComplaintModel;
use App\Models\Message\MessageOrderModel;
use App\Models\UserModel;
use App\Models\MessageModel;
use App\Models\Message\Message_logModel;
use App\Models\Message\ReplyModel;
use App\Models\Message\ForemailModel;
use App\Models\Message\SendemailModel;
use App\Models\Message\Template\TypeModel;
use App\Models\Message\AccountModel;
use App\Models\OrderModel;
use Channel;
use Translation;
use App\Jobs\SendMutiMessages;



class MessageController extends Controller
{
    public function __construct(MessageModel $message)
    {
        $this->model = $message;
        $this->mainIndex = route('message.index');
        $this->mainTitle = '信息';
        $this->viewPath = 'message.';
        $this->workflow = request()->session()->get('workflow');
    }

    public function testGetMessgae()
    {
        $job =  new SendMutiMessages('test');
        $job = $job->onQueue('SendMutiMessages');
        $this->dispatch($job);
        dd(234234);


        $account = AccountModel::find(10);
        dd($account->accountLabels->where('is_get_mail', 'get')->pluck('label_id'));
            dd(234);
        $adpter = Channel::driver($account->channel->api_type, $account->api_config);
        $reply = ReplyModel::find(21922);

        dd($adpter->sendMessages($reply));

    }

    public function index()
    {
        //dd( $this->model->mixed_search);
        request()->flash();
        $users=UserModel::where('is_login', '1')->get();
        $users_leaders = UserModel::where('is_login', '1')->whereIn('group', ['leader','super'])->get()->pluck('id');
        $users_leaders_arr = array();
        if (!empty($users_leaders)){
            foreach ($users_leaders as $key=>$users_leader)
            {
                $users_leaders_arr[$key]=$users_leader;
            }
        }
        $indexFlag = 1;
        $pageSize = request()->has('pageSize') ? request()->input('pageSize') : config('setting.pageSize');
        $response = [
            'metas' => $this->metas(__FUNCTION__),
//            'data' => $this->autoList($this->model->where('label', 'INBOX')),
            'data' => $this->autoList($this->model, $fields = ['*'], $pageSize),
            'users' => $users,
            'users_leader' => $users_leaders_arr,
            'indexFlag' => $indexFlag,
            'mixedSearchFields' => $this->model->mixed_search,
        ];
        return view($this->viewPath . 'index', $response);
    }

    public function content($id)
    {
        $model = $this->model->find($id);
        if (!$model) {
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', $this->mainTitle . '不存在.'));
        }
        return $model->message_content;
    }

    public function systemList()
    {
        request()->flash();
        $response = [
            'metas' => $this->metas(__FUNCTION__),
            'data' => $this->autoList($this->model->where('label', 'SENT')),
        ];
        return view($this->viewPath . 'sentList', $response);
    }

    public function startWorkflow()
    {
        request()->session()->put('workflow', 'keeping');
        return redirect(route('message.process'))
            ->with('alert', $this->alert('success', '工作流已开启.'));
    }

    public function endWorkflow($id)
    {
        request()->session()->pull('workflow');
        return redirect(route('message.process', ['id' => $id]))
            ->with('alert', $this->alert('danger', '工作流已终止.'));
    }

    public function processtest()
    {
        if (request()->input('id')) {
            $message = $this->model->find(request()->input('id'));
        } elseif ($this->workflow == 'keeping') {
            $message = $this->model->getOne(request()->user()->id);
        } else {
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', 'error.'));
        }
		
        if (!$message) {
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', '信息不存在.'));
        }
        if(request()->input('id')){
            $model = $this->model->find(request()->input('id'));
            $count = $this->model->where('from','=',$model->from)->where('status','=','UNREAD')->count();
        }else{
            $count='';
        }
        /*
       $body=$message->message_content;
       //正则匹配邮件内容
       $htmlBody=preg_match_all('/ordernum:([0-9]{1,})<br/si', $body);
       //$content_email=preg_match_all("^[_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3}$",$body);
       //$content_email=$this->getNeedBetween($body,'邮箱:','<br');
       echo "123";
       //var_dump($content_email);
       exit;
       $content_email_true=strpos($body,'邮箱:');
       if($content_email_true){
           $ordernum=array();
           if(!$htmlBody){
               $ordernum=$message->guessGetOrdersnum(request()->input('email'));
               if(count($ordernum)>1){
                   $ordernum=$ordernum;
               }
           }
       }
       */
	   
        $inputemail=request()->input('email');
        if(!$inputemail){
            if($message->from=="pre-sale@choies.com"){
                $body=$message->message_content;
                preg_match_all('/邮箱:(.*?)<br>/',$body,$match);
                if(count($match[1])){
                    $inputemail=$match[1][0];
                }else{
                    $inputemail="";
                }
            }
        }

        
        if ($message->assign(request()->user()->id)) {
            $userarr=config('user.staff');
            $emailarr=config('user.email');
            $complaint_arr=config('setting.complaints');
            $settled_arr=config('setting.settled');
            $response = [
                'metas' => $this->metas(__FUNCTION__),
                'message' => $message,
                'parents' => TypeModel::where('parent_id', 0)->get(),
                'parent_type' => TypeModel::where('parent_id','<>', 0)->get(),
                'users' => UserModel::whereIn('id', $userarr)->get(),
                'complaint_arr' => $complaint_arr,
                'settled_arr' => $settled_arr,
                'emailarr' => $emailarr,
                'relatedOrders' => $message->related == 0 ? $message->guessRelatedOrders($inputemail) : '',
                //'ordernum' =>$ordernum,
                'accounts'=>AccountModel::all(),
            ];
            return view($this->viewPath . 'process', $response)->with('count',$count);

        }
        return redirect($this->mainIndex)->with('alert', $this->alert('danger', '该信息已被他人处理.'));
    }

    public function process()
    {
        if (request()->input('id')) {
            $message = $this->model->find(request()->input('id'));
        } elseif ($this->workflow == 'keeping') {
            $message = $this->model->getOne(request()->user()->id);
        } else {
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', 'error.'));
        }
        
        if (!$message) {
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', '信息不存在.'));
        }
        if(request()->input('id')){
            $model = $this->model->find(request()->input('id'));
            $count = $this->model->where('from','=',$model->from)->where('id', '<>', request()->input('id'))->where('from','<>','')->where('status','=','UNREAD')->count();
        }else{
            $count='';
        }
/*        if($message->from=="pre-sale@choies.com")
        {
            $body=$message->message_content;
            preg_match_all('/ordernum:(.*?)<br>/',$body,$match);
            if(count($match[1])){
                $orderNums[]=$match[1][0];
                $k->setRelatedOrders($orderNums);
            }
        }*/

        $inputemail=request()->input('email');
        if(!$inputemail){
            if($message->from=="pre-sale@choies.com"){
                $body=$message->message_content;
                preg_match_all('/邮箱:(.*?)<br>/',$body,$match);
                if(count($match[1])){
                    $inputemail=$match[1][0];
                }else{
                    $inputemail="";
                }
            }
        }

        if($message->related != 1)
        {
            if($message->from=="livechat@comm100chat.com")
            {
                $body=$message->message_content;
                preg_match_all('/<td>(.*?)<\/td>/', $body,$match);
                if(count($match[1]))
                {
                    $email=$match[1][5];
                    $ordernum = OrderModel::OfOrderemail($email)->first();
                    if($ordernum)
                    {
                        $orderNums[] = $ordernum->ordernum;
                        $message->setRelatedOrders($orderNums);                        
                    }

                }
            }         
        }

        $inputemail=request()->input('email');

        if(!$inputemail){
            if($message->from=="pre-sale@choies.com"){
                $body=$message->message_content;
                preg_match_all('/Email:(.*?)<br>/',$body,$match);
                if(count($match[1])){
                    $inputemail=$match[1][0];
                }else{
                    preg_match_all('/邮箱:(.*?)<br>/',$body,$match);
                    if(count($match[1])) {
                        $inputemail = $match[1][0];
                    }else{
                        $inputemail="";
                    }
                }
            }
        }

        // 获取关联order_ids
        $order_ids = array();
        if($message->related ==1)
        {
            $orders =\App\Models\Message\OrderModel::where('message_id', $message->id)->get();
            foreach ($orders as $key=>$value)
            {
                $order_ids[] = array(
                    "order_id" => $value['order_id'],
                    "msg_orders_id" => $value['id'],
                    "created_at" => $value['created_at']
                );
            }
        }

        if ($message->assign(request()->user()->id)) {
            $userarr=config('user.staff');
            $emailarr=config('user.email');
            $complaint_arr=config('setting.complaints');
            $settled_arr=config('setting.settled');
            $response = [
                'metas' => $this->metas(__FUNCTION__),
                'message' => $message,
                'parents' => TypeModel::where('parent_id', 0)->get(),
                'parent_type' => TypeModel::where('parent_id','<>', 0)->get(),
                'users' => UserModel::where('is_login', '1')->get(),
                'complaint_arr' => $complaint_arr,
                'settled_arr' => $settled_arr,
                'emailarr' => $emailarr,
                'relatedOrders' => $message->related == 0 ? $message->guessRelatedOrders($inputemail) : '',
                //'ordernum' =>$ordernum,
                'erp_apiOrderinfo' =>$this->getOrderInfo_erp($order_ids),
                'accounts'=>AccountModel::all(),
            ];
            //自动关联
            if ($message->related != 1){
                if (isset($response['relatedOrders']['email']) && count($response['relatedOrders']['email']) == 0) {
                    //无订单关联的不需要点击无需关联按钮才能进行回复
                    $message->notRelatedOrder($message->id);
                } elseif (isset($response['relatedOrders']['email']) && count($response['relatedOrders']['email']) == 1) {
                    //点了取消订单按钮，flag=1，则不自动关联，session仅存一个取消位。session失效后则自动关联。
                    $cancelRelatedOrderFlag=request()->session()->get('cancelRelatedOrderFlag');
                    $cancelRelatedOrderFlags[]=explode('-',$cancelRelatedOrderFlag);
                    $flag=0;
                    foreach($cancelRelatedOrderFlags as $cancelFlag){
                        if(isset($cancelFlag[0]) && isset($cancelFlag[1])
                            &&$cancelFlag[0]==$message->id &&$cancelFlag[1]==1) {
                            $flag = 1;
                        }
                    }
                    if($flag==0) {
                        //自动关联
                        if ($response['relatedOrders']['email'][0] && $response['relatedOrders']['email'][0]['ordernum']) {
                            $orderNums[] = $response['relatedOrders']['email'][0]['id'];
                            $message->setRelatedOrders($orderNums);

                            // 获取关联order_ids
                            $order_ids = array();
                            if($message->related ==1)
                            {
                                $orders =\App\Models\Message\OrderModel::where('message_id', $message->id)->get();
                                foreach ($orders as $key=>$value)
                                {
                                    $order_ids[] = array(
                                        "order_id" => $value['order_id'],
                                        "msg_orders_id" => $value['id'],
                                        "created_at" => $value['created_at']
                                    );
                                }
                            }
                            $response['erp_apiOrderinfo'] =$this->getOrderInfo_erp($order_ids);
                        }
                    }
                }
            }
            return view($this->viewPath . 'process', $response)->with('count',$count);

        }
        return redirect($this->mainIndex)->with('alert', $this->alert('danger', '该信息已被他人处理.'));
    }

    public function process1($id)
    {
        if ($this->workflow == 'keeping') {
            $message = $this->model->getOne1(request()->user()->id,$id);
        } else {
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', 'error.'));
        }

        if (!$message) {
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', '信息不存在.'));
        }
        if(request()->input('id')){
            $model = $this->model->find(request()->input('id'));
            $count = $this->model->where('from','=',$model->from)->where('status','=','UNREAD')->count();
        }else{
            $count='';
        }
        if ($message->assign(request()->user()->id)) {
            $emailarr=config('user.email');
            $response = [
                'metas' => $this->metas(__FUNCTION__),
                'message' => $message,
                'parents' => TypeModel::where('parent_id', 0)->get(),
                'parent_type' => TypeModel::where('parent_id','<>', 0)->get(),
                'users' => UserModel::all(),
                'emailarr' => $emailarr,
                'relatedOrders' => $message->related == 0 ? $message->guessRelatedOrders(request()->input('email')) : '',
                //'ordernum' =>$ordernum,
                'accounts'=>AccountModel::all(),
            ];
            return view($this->viewPath . 'process', $response)->with('count',$count);

        }
        return redirect($this->mainIndex)->with('alert', $this->alert('danger', '该信息已被他人处理.'));
    }

    public function reply($id, ReplyModel $reply)
    {
        $message = $this->model->find($id);
        $type_id=request()->input('type_id1');
        $reference_url = request()->input('reference_url');
        $all=request()->all();
        if($type_id){
            $all['type_id']=$type_id;
        }
        if (!$message) {
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', '信息不存在.'));
        }
        request()->flash();
		//var_dump($id);
        $this->validate(request(), $reply->rules('create'));
        if ($message->reply(request()->all())) {
            // if required=0 则设置为1.标记回复 by xl 20160819
            if (isset($message->required) && $message->required == '0'){
                $data1['required']='1';
                $message->update($data1);
            }

            /**
             * 写入发送队列
             */
            $reply = $reply->where('message_id',$id)->orderBy('id','DESC')->get()->first();
            $job = new SendMutiMessages($reply);
            $job = $job->onQueue('SendMutiMessages');
            $this->dispatch($job);

            if ($this->workflow == 'keeping') {
                return redirect(route('message.process'))
                    ->with('alert', $this->alert('success', '上条信息已成功回复.'));
            }
            return redirect($reference_url)->with('alert', $this->alert('success', '回复成功.'));
        }
        return redirect($reference_url)->with('alert', $this->alert('danger', '回复失败.'));
    }

    public function setRelatedOrders($id)
    {
        $message = $this->model->find($id);
        if (!$message) {
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', '信息不存在.'));
        }
        $numbers = request()->input('relatedOrdernums');
        if (request()->input('numbers')) {
            foreach (explode(',', request()->input('numbers')) as $number) {
                $numbers[] = $number;
            }
        }else{
            if($message->from=="pre-sale@choies.com"){
                $body=$message->message_content;
                preg_match_all('/ordernum:(.*?)<br>/',$body,$match);
                if(count($match[1])){
                    $numbers[]=$match[1][0];
                }
            }
        }

        if ($message->setRelatedOrders($numbers)) {
            $alert = $this->alert('success', '关联订单成功.');
        } else {
            $alert = $this->alert('danger', '关联订单失败.');
        }
        return redirect(route('message.process', ['id' => $id]))->with('alert', $alert);
    }

    public function cancelRelatedOrder($id, $relatedOrderId)
    {
        $message = $this->model->find($id);
        if (!$message) {
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', '信息不存在.'));
        }
        if ($message->cancelRelatedOrder($relatedOrderId)) {
            $alert = $this->alert('success', '取消订单关联成功.');
        } else {
            $alert = $this->alert('danger', '取消订单关联失败.');
        }
        //设置取消关联标记位
        request()->session()->put('cancelRelatedOrderFlag', $id.'-1');
        return redirect(route('message.process', ['id' => $id]))->with('alert', $alert);
    }

    public function notRelatedOrder($id)
    {
        $message = $this->model->find($id);
        if (!$message) {
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', '信息不存在.'));
        }
        if ($message->notRelatedOrder()) {
            $alert = $this->alert('success', '无需关联订单设置成功.');
        } else {
            $alert = $this->alert('danger', '无需关联订单设置失败.');
        }
        return redirect(route('message.process', ['id' => $id]))->with('alert', $alert);
    }

    public function assignToOther($id)
    {
        $message = $this->model->find($id);
        $touser=UserModel::find(request()->input('assign_id'))->name;
        if (!$message) {
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', '信息不存在.'));
        }
        if ($message->assignToOther(request()->user()->id ,request()->input('assign_id'))) {
            $data=array();
            $data['message_id']=$id;
            $data['foruser']=request()->user()->name;
            $data['assign_id']=request()->input('assign_id');
            $data['touser']=$touser;
            Message_logModel::create($data);
            if ($this->workflow == 'keeping') {
                return redirect(route('message.process'))
                    ->with('alert', $this->alert('success', '上条信息已转交他人.'));
            }
            return redirect($this->mainIndex)->with('alert', $this->alert('success', '转交成功.'));
        }
        return redirect($this->mainIndex)->with('alert', $this->alert('danger', '转交失败.'));
    }

    public function notRequireReply($id)
    {
        $message = $this->model->find($id);
        if (!$message) {
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', '信息不存在.'));
        }
        if ($message->notRequireReply(request()->user()->id)) {
            if ($this->workflow == 'keeping') {
                return redirect(route('message.process'))
                    ->with('alert', $this->alert('success', '上条信息已标记无需回复.'));
            }
            return redirect($this->mainIndex)->with('alert', $this->alert('success', '处理成功.'));
        }
        return redirect($this->mainIndex)->with('alert', $this->alert('danger', '处理失败.'));
    }
	
	//转发邮件
	public function foremail($id, ForemailModel $reply, MessageModel $message)
	{
        $this->model=$reply;
		$message = $message->find($id);
        $data=array();
        if(request()->input('email')){
            $data['message_id']=$id;
            $data['to']= $message->to ? $message->to : '';
            $data['to_email']=request()->input('email');
            $data['to_useremail']=$message->from;
            $data['title']=$message->title ? $message->title : '';
            $data['content']=$message->message_content;
            $comment = $reply->create($data);
            if ($message->notRequireReply(request()->user()->id)) {
                if ($this->workflow == 'keeping') {
                    return redirect(route('message.process'))
                        ->with('alert', $this->alert('success', '转发邮件保存成功.'));
                }
                return redirect($this->mainIndex)->with('alert', $this->alert('success', '转发邮件保存成功!'));
            }
        }else{
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', '邮箱不能为空!'));
        }
	}


    //稍后处理
    public function dontRequireReply($id)
    {
        $message = $this->model->find($id);
        if (!$message) {
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', '信息不存在.'));
        }
        if ($message->dontRequireReply(request()->user()->id)) {
            if ($this->workflow == 'keeping') {
                return redirect(route('message.process1',['id'=>$id]))
                    ->with('alert', $this->alert('success', '上条信息已标记稍后处理.'));
            }
            return redirect($this->mainIndex)->with('alert', $this->alert('success', '处理成功.'));
        }
    }


    //新增无需回复批量处理
    public function notsRequireReply(MessageModel $message)
    {
        $ids=request()->input('ids');
        $ids = explode("\n", $ids);
        foreach ($ids as $key => $value) {
            $message = $message->find($value);
            if($message->status!="COMPLETE"){
                $message->assign_id=request()->user()->id;
                $message->required=0;
                $message->status="COMPLETE";
                $message->save();
            }
        }
        echo "<script>alert('批量无需回复处理成功.');window.location.href = document.referrer;</script>";
    }

    //新增单个无需回复处理
    public function notRequireReply_1($id)
    {
        $message = $this->model->find($id);
        if($message->status!="COMPLETE"){
            $message->assign_id=request()->user()->id;
            $message->required=0;
            $message->status="COMPLETE";
            $message->save();
        }
        echo "<script>alert('无需回复处理成功.');window.location.href = document.referrer;</script>";
    }

    //新增批量分配邮件
    public function assigned(MessageModel $message)
    {
        $ids=request()->input('ids');
        $assign_id=request()->input('assign_id');
        if($assign_id=="请选择"){
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', '分配失败,客服不能为空!'));
        }
        $ids = explode("\n", $ids);
        foreach ($ids as $key => $value) {
            $message = $message->find($value);
            $message->assign_id=$assign_id;
            $message->required=0;
            $message->status="PROCESS";
            $message->save();
        }
        return redirect($this->mainIndex)->with('alert', $this->alert('success', '分配成功!'));
    }


    //测试自动分配
    public function showkai(){
        $k=MessageModel::find(4608);
        $userId = $k->last->assign_id;
        $kai=$k->message_content;
        var_dump($kai);
        exit;
    }

    //上传文件
    public function updatefile(){
        $uploaddir ='/uploads/';//设置文件保存目录 注意包含/
        //$patch="/uploads";//程序所在路径
        $basedir = public_path();
        $basedir = str_replace('\\',"/",$basedir);
        if ($_FILES["upfile"]["error"] > 0)
        {
            echo "Error: " . $_FILES["upfile"]["error"] . "<br />";
        }else{
            $filename=explode(".",$_FILES['upfile']['name']);
            $filename[0]=rand(1,1000000000); //设置随机数长度
            $name=implode(".",$filename);
            //$name1=$name.".Mcncc";
            $uploadfile=$basedir.$uploaddir.$name;
            if (move_uploaded_file($_FILES['upfile']['tmp_name'],$uploadfile)){
                $upload_file=$uploaddir.$name;
                echo "<script>parent.callback('$upload_file',true)</script>";
            }else{
                echo "<script>parent.callback('$uploadfile',false)</script>";
            }
        }
        exit;

    }

    //上传速卖通图片
    public function updateimg($id){
        $uploaddir ='/uploads/aliexpress/';//设置文件保存目录 注意包含/
        //$patch="/uploads";//程序所在路径
        $basedir = public_path();
        $basedir = str_replace('\\',"/",$basedir);
        if ($_FILES["upfile"]["error"] > 0)
        {
            echo "Error: " . $_FILES["upfile"]["error"] . "<br />";
        }else{
            $filename=explode(".",$_FILES['upfile']['name']);
            $filename[0]=rand(1,1000000000); //设置随机数长度
            $name=implode(".",$filename);
            //$name1=$name.".Mcncc";
            $uploadfile=$basedir.$uploaddir.$name;
            if (move_uploaded_file($_FILES['upfile']['tmp_name'],$uploadfile)){
                $upload_file=$uploaddir.$name;
                #
                $message = $this->model->find($id);
                $adpter = Channel::driver($message->channel->api_type, $message->account->api_config);
//                $file = 'http://crm1.jinjidexiaoxuesheng.com' . $upload_file;
                $file = 'http://52.78.109.226' . $upload_file;
                $result = $adpter->uploadBankImage('api.uploadTempImage',$file); //获取Message列表
                if (isset($result['url'])){
                    $smt_return_img = $result['url'];
                    echo "<script>parent.callback('$upload_file',true,'$smt_return_img')</script>";
                }else {
                    echo "<script>parent.callback('$uploadfile',false,'')</script>";
                }

            }else{
                echo "<script>parent.callback('$uploadfile',false,'')</script>";
            }
        }
        exit;

    }

    // CRM对接ERP,根据order_id获取order相关信息
    public function getOrderInfo_erp($order_ids)
    {
        $result = array();
        foreach($order_ids as $key=>$order_id)
        {
            if($order_id['created_at'] > config('setting.erpData'))
            {
                $post_data = array();
                $post_data['order_id'] = $order_id['order_id'];
                $url = "http://erp.wxzeshang.com:8000/api/crm_get_order_info/";
                $ch = curl_init();
                curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT,20);
                curl_setopt ( $ch, CURLOPT_TIMEOUT,20);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                $json_order_info = curl_exec($ch);
                if (curl_errno($ch)) {
                    echo 'Curl error: ' . curl_error($ch);
                    return;
                }
                curl_close($ch);
                $order_info = json_decode($json_order_info, true);
                $order_info['msg_orders_id'] = $order_id['msg_orders_id'];
                # 根据order_num获取投诉信息
                $relatemessage = MessageOrderModel::where('ordernum', $order_info['ordernum'])->get();
                $order_info['relatedMessage'] = $relatemessage;
                $relatedCom = MessageComplaintModel::where('ordernum', $order_info['ordernum'])->get()->last();
                $order_info['relatedCom'] = $relatedCom;
                $result[] = $order_info;
            }
        }
        return $result;
    }

    /**
     * ajax获取百度翻译
     */
    public function ajaxGetTranInfo(){

        $content = request()->input('content');
        if(!empty($content)){
            $result = Translation::translate($content);
        }else{
            $result = false;
        }
        // echo json_encode(['content'=>'翻译结果','status'=>config('status.ajax.success')]);exit;
        if(isset($result['error_code'])){
            echo json_encode(['status'=>config('status.ajax.fail')]);
        }else{
            echo json_encode(['content'=>$result['trans_result'][0]['dst'],'status'=>config('status.ajax.success')]);
        }
    }

}