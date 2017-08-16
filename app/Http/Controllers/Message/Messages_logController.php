<?php
/**
 * 回复队列控制器
 *
 * 2016-02-01
 * @author: Vincent<nyewon@gmail.com>
 */

namespace App\Http\Controllers\Message;

use App\Http\Controllers\Controller;
use App\Models\Message\Message_logModel;

class Messages_logController extends Controller
{
    public function __construct(Message_logModel $reply)
    {
        $this->model = $reply;
        $this->mainIndex = route('message_log.index');
        $this->mainTitle = '转交历史';
        $this->viewPath = 'message.message_log.';

    }

}