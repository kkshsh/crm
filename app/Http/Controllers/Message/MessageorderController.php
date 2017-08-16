<?php
/**
 * 回复队列控制器
 *
 * 2016-02-01
 * @author: Vincent<nyewon@gmail.com>
 */

namespace App\Http\Controllers\Message;

use App\Models\UserModel;
use App\Http\Controllers\Controller;
use App\Models\Message\SendemailModel;
use App\Models\Message\MessageComplaintModel;
use App\Models\Message\MessageOrderModel;
use App\Models\Message\Template\TypeModel;

class MessageorderController extends Controller
{
    public function __construct(MessageOrderModel $reply)
    {
        $this->model = $reply;
        $this->mainIndex = route('messageorder.index');
        $this->mainTitle = '投诉类型';
        $this->viewPath = 'message.messageorder.';    }

    public function create()
    {
        $response = [
            'metas' => $this->metas(__FUNCTION__),
            'parents' => TypeModel::where('parent_id', 0)->get(),
            'users' => UserModel::all(),
        ];
        return view($this->viewPath . 'create', $response);
    }

    //保存直接发送邮件到库
    public function save(MessageOrderModel $messageorder)
    {
        $arr=array();
        $data=array();
        $kai="";
        $ordernum=$_POST['ordernum'];
        $message_id=$_POST['message_id'];
        $settled_name=$_POST['settled_name'];
        $email=$_POST['email'];
        $refund=$_POST['refund'];
        $complaint_arr=config('setting.complaints');
        if($_POST['message_id']){
            $str=$_POST['str'];
            //保存投诉表
            $a['email']=$email;
            $a['ordernum']=$ordernum;
            $a['settled_name']=$settled_name;
            $a['message_id']=$message_id;
            $a['refund']=$refund;
            $a['created']= time();
            $state=$this->model->create($a);
            $str_arr=array_filter(explode(";",$str));
            foreach ($str_arr as $key => $value) {
                $str_arrid=explode(",",$str_arr[$key]);
                if($str_arrid[3]!="normal" && $str_arrid[3]!="正常"){
                    $complaints=$complaint_arr[$str_arrid[3]];
                    $data['sku']=$str_arrid[0];
                    $data['qty']=$str_arrid[1];
                    $data['price']=$str_arrid[2];
                    $data['com_name']=$complaints[$str_arrid[4]];

                    $data['message_id']=$message_id;
                    $data['ordernum']=$ordernum;
                    $data['content']=$str_arrid[5];
                    $data['com_id']=$state->id;
                    if($str_arrid[5]=="null"){
                        $data['content']="";
                    }
                    if($str_arrid[3]=="logistics"){
                        $data['com']="物流问题";
                    }elseif($str_arrid[3]=="sendwrong"){
                        $data['com']="错发漏发";
                    }elseif($str_arrid[3]=="quality"){
                        $data['com']="质量问题";
                    }elseif($str_arrid[3]=="sizewrong"){
                        $data['com']="尺码问题";
                    }elseif($str_arrid[3]=="picturewrong"){
                        $data['com']="图货不一";
                    }elseif($str_arrid[3]=="other"){
                        $data['com']="其他问题";
                    }
                    $messageorder->create($data);
                }
            }
            $status['success']=1;
            $status['content']="提交成功";
            echo json_encode($status);
        }else{
            $status['success']="0";
            $status['content']="投诉类型布不能为空~!";
            echo json_encode($status);
        }
    }


    //导表
    public function export()
    {
        $start_time=request()->input('start_time');
        $end_time=request()->input('end_time');
        $messages=$this->model->where('created_at','>',$start_time)->where('created_at','<=',$end_time)->get();
        $kai="";
        if($start_time && $end_time) {
            header('Content-Type: application/vnd.ms-excel charset=utf-8');
            header('Content-Disposition: attachment; filename="投诉类型表.csv"');
            echo "日期,单号,Package,客户邮箱,国家,投诉产品SKU,price,产品数量,投诉类型,投诉原因类型,投诉具体,解决方案,退款金额\n";
            foreach ($messages as $key => $value) {
                echo $value->created_at->format('Y-m-d'), ',';
                echo $value->ordernum, ',';
                echo $value->packageid, ',';
                foreach ($value->assigner1 as $k => $v) {
                    echo $v->email,',';
                }
                if(isset($value->assigner2[0])){
                    echo $value->assigner2[0]->shipping_country, ',';
                }else{
                    echo ' ,';
                }
                echo $value->sku, ',';
                echo $value->price, ',';
                echo $value->qty, ',';
                echo $value->com, ',';
                echo $value->com_name, ',';
                echo $value->content, ',';
                foreach ($value->assigner1 as $k => $v) {
                    echo $v->settled_name,',';
                    echo $v->refund,',';
                }
                echo  ',', PHP_EOL;
            }
            exit;
        }
    }


    //
    public function ajaxGetChildren1(){
        $comname=$_POST['comname'];
        $complaint_arr=config('setting.complaints');
        return json_encode($complaint_arr[$comname]);
    }


}