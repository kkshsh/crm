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
use App\Models\Message\Template\TypeModel;

class SendemailController extends Controller
{
    public function __construct(SendemailModel $reply)
    {
        $this->model = $reply;
        $this->mainIndex = route('sendemail.index');
        $this->mainTitle = '直接发邮件';
        $this->viewPath = 'message.sendemail.';
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
    public function save()
    {
        $data=array();
        if(request()->input('to_email')){
            $data['to']= "service@choies.com";
            $data['to_email']=request()->input('to_email');
            $data['title']=request()->input('title');
            $data['content']=request()->input('content');
            $this->model->create($data);
            return redirect($this->mainIndex)->with('alert', $this->alert('success', '保存成功!'));
        }else{
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', '邮箱不能为空!'));
        }
    }

    //保存直接发送邮件到库(带附件)
    public function saveFile()
    {
        if (request()->input('to_email')==false) {
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', '邮箱不能为空!'));
        }
        //邮件内容
        $content_old = request()->input('content');
        //上传文件
        $uploaddir = '/uploads/';//设置文件保存目录 注意包含/
        if (is_dir("uploads") == false) {
            mkdir("uploads");
        }
        //$patch="/uploads";//程序所在路径
        $basedir = public_path();
        $basedir = str_replace('\\', "/", $basedir);
        //统计error数
        $errorCount=0;
        $errorContent="";
        $upload_file_full="";
        $upload_file_href="";
        for($i=1;$i<count($_FILES["upfile"]["error"])+1;$i++){
            if ($_FILES["upfile"]["error"][$i] > 0 && $_FILES["upfile"]["error"][$i] != 4) {
                $errorContent.= "第" . $i . "个附件Error: " . $_FILES["upfile"]["error"][$i] . "<br />";
                $errorCount=$errorCount+1;
            }
        }
        if($errorCount==0){
            for($i=1;$i<count($_FILES["upfile"]["error"])+1;$i++){
                if ($_FILES["upfile"]["error"][$i]==0)
                {
                    $filename = explode(".", $_FILES['upfile']['name'][$i]);
                    $filename[0] = rand(1, 1000000000); //设置随机数长度
                    $name = implode(".", $filename);
                    $uploadfile = $basedir . $uploaddir . $name;
                    if (move_uploaded_file($_FILES['upfile']['tmp_name'][$i], $uploadfile) == false) {
                        return redirect($this->mainIndex)->with('alert', $this->alert('danger', '附件上传失败!'));
                    }
                    $upload_file_full.=$uploaddir . $name.";";
                    $upload_file= $uploaddir . $name;
                    // content+附件链接
                    $upload_file_href .= "<br>http://crm.jinjidexiaoxuesheng.com".$upload_file;
                }
            }
            //保存直接发送邮件信息到库
            $data = array();
            $data['to'] = "service@choies.com";
            $data['to_email'] = request()->input('to_email');
            $data['title'] = request()->input('title');
            $content_new=$content_old.$upload_file_href;
            $data['content'] = $content_new;
            $data['updatefile'] = $upload_file_full;
            $this->model->create($data);
            return redirect($this->mainIndex)->with('alert', $this->alert('success', '保存成功!'));
        }else{
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', '附件上传失败!'.$errorContent));
        }
    }

}