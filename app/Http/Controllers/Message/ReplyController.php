<?php
/**
 * 回复队列控制器
 *
 * 2016-02-01
 * @author: Vincent<nyewon@gmail.com>
 */

namespace App\Http\Controllers\Message;

use App\Http\Controllers\Controller;
use App\Models\Message\ReplyModel;
use App\Jobs\SendMutiMessages;

class ReplyController extends Controller
{
    public function __construct(ReplyModel $reply)
    {
        $this->model = $reply;
        $this->mainIndex = route('messageReply.index');
        $this->mainTitle = '回复队列';
        $this->viewPath = 'message.reply.';
        if (!in_array(request()->user()->group, ['leader', 'super'])) {
            exit($this->alert('danger', '无权限'));
        }
    }
    // 手动发送未发送出去的邮件
    public function  replysendmsg($id)
    {
        $reply = $this->model->where('id',$id)->orderBy('id','DESC')->get()->first();
        $job = new SendMutiMessages($reply);
        $job = $job->onQueue('SendMutiMessages');
        $this->dispatch($job);
        echo 'id:'.$id." has send message ok!";
    }

}