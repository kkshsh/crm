<?php
/**
 * 回复队列控制器
 *
 * 2016-02-01
 * @author: Vincent<nyewon@gmail.com>
 */

namespace App\Http\Controllers\Message;

use App\Http\Controllers\Controller;
use App\Models\Message\ForemailModel;

class ForemailController extends Controller
{
    public function __construct(ForemailModel $reply)
    {
        $this->model = $reply;
        $this->mainIndex = route('forwardemail.index');
        $this->mainTitle = '邮件转发';
        $this->viewPath = 'message.foremail.';

    }

}