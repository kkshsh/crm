<?php
/**
 * Created by PhpStorm.
 * User: Vincent
 * Date: 16/1/27
 * Time: 上午9:19
 */

namespace App\Http\Controllers;
use App\Models\MessageModel;
use App\Models\Message\AccountModel;
use DB;

class ApichoiesController extends Controller
{

    public function __construct(MessageModel $message)
    {
        $this->model = $message;
    }
    /**
     * 新建
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $data=array();
        if(isset($_POST['email'])){
            if($_POST['toemail']=='service@choies.com' || $_POST['toemail']=='tracking@choies.com' || $_POST['toemail']=='complaint@choies.com'){
                $account_id=AccountModel::where('account', '=', $_POST['toemail'])->first();
                $account_id=$account_id->id;
            }else{
                $account_id='';
            }
            $data['account_id']=$account_id;
            $data['message_id']='';
            $data['mime_type']='';
            $data['subject']='Re:';
            $data['start_at']='';
            $data['date']='';
            $data['related']=1;
            $data['required']=1;
            $data['read']=0;
            $data['status']='UNREAD';
            $data['label']='INBOX';
            $data['labels']='inbox';
            $data['from']=$_POST['email'];
            $data['from_name']=$_POST['username'];
            $data['to']=$_POST['toemail'];
            $data['content']=$_POST['message']."<br/>ordernum : ".$_POST['order_num'];
        } 
        messagemodel::create($data);
        exit;
        exit;
        if($message->create($data)){
            return "success";
        }else{
            return "error";
        }
        exit;
    }
}