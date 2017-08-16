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

class ComplaintController extends Controller
{
    public function __construct(MessageComplaintModel $reply)
    {
        $this->model = $reply;
        $this->mainIndex = route('complaint.index');
        $this->mainTitle = '投诉类型';
        $this->viewPath = 'message.complaint.';
    }

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
        $assign_id=$_POST['assign_id'];
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
                    $data['assign_id']=$assign_id;
                    $data['ordernum']=$ordernum;
                    # add packageid
                    $data['packageid']=$str_arrid[6];
                    $data['content']=$str_arrid[5];
                    # add refund_amount
                    $data['refund_amount']=$str_arrid[7];
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
                    $kai .=$data['sku'].",".$data['com_name'];
                    $messageorder->create($data);
                }
            }
            $assignint=intval($_POST['assign_id']);
            $post_data['assgin_name'] = UserModel::find($assignint)->name;
            $post_data1=$kai.",".$settled_name.":(币种)".$_POST['currency'].":".$_POST['refund']."--处理人:".$post_data['assgin_name'];
            $kk['ws_return']=$post_data1;
            $this->model->find($state->id)->update($kk);
            //给风控传值
            //部分退款  和  全额退款  给风控传值
            if($settled_name=="部分退款" || $settled_name=="全额退款"){
                $url='http://manage.choiesriskcontrol.com/api/getcrmrefund';
                $post_data=array();
                $post_data['ordernum'] = $_POST['ordernum'];
                $post_data['sku'] = $str_arrid[0];
                $assignint=intval($_POST['assign_id']);
                $post_data['assgin_name'] = UserModel::find($assignint)->name;
                $post_data['com'] = $kai.",".$settled_name.":(币种)".$_POST['currency'].":".$_POST['refund']."--处理人:".$post_data['assgin_name'];
                $ch = curl_init ();
                curl_setopt ( $ch, CURLOPT_URL, $url );
                curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
                curl_setopt ( $ch, CURLOPT_POST, true );
                curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_data);
                $product_info = curl_exec ( $ch );
                curl_close($ch);
                $json_info=json_decode($product_info);
                if($json_info->success==1){
                    $status['success']=1;
                    $status['content']=$json_info->msg;
                    echo json_encode($status);
                }else{
                    $status['success']=0;
                    $status['content']=$json_info->msg;
                    echo json_encode($status);
                }
            }else{
                $status['success']=1;
                $status['content']="提交成功";
                echo json_encode($status);
            }

        }else{
            $status['success']="0";
            $status['content']="投诉类型布不能为空~!";
            echo json_encode($status);
        }
    }

    //
    public function ajaxGetChildren1(){
        $comname=$_POST['comname'];
        $complaint_arr=config('setting.complaints');
        return json_encode($complaint_arr[$comname]);
    }


}