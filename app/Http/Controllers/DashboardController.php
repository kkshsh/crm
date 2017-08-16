<?php

namespace App\Http\Controllers;

use App\Models\MessageModel;
use App\Models\Message\ReplyModel;
use App\Models\UserAccountModel;

class DashboardController extends Controller
{
    public function __construct(MessageModel $message)
    {
        $this->model = $message;
        $this->mainIndex = route('dashboard.index');
        $this->mainTitle = 'Dashboard';
        $this->viewPath = 'dashboard.';
    }

    public function index()
    {
        request()->flash();
        $user_me_id = request()->user()->id;
        # 我的未读邮件数
        $me_unread_count = MessageModel::Where('assign_id', $user_me_id)->where('status',('UNREAD'))->get()->count();
        # 我的待处理邮件数
        $me_process_count = MessageModel::Where('assign_id', $user_me_id)->where('status',('PROCESS'))->get()->count();
        # 我的已回复邮件数
        $me_complete_count = MessageModel::Where('assign_id', $user_me_id)->whereIn('status',array('COMPLETE',))->get()->count();
        # 发送失败监控
        $reply_fail_count = ReplyModel::whereIn('status', ['FAIL','NEW'])->get()->count();
        # 我负责的渠道账号
        $user_accounts = UserAccountModel::where('user_id',$user_me_id)->get();
        $response = [
            'metas' => $this->metas(__FUNCTION__),
            'data' => $this->autoList($this->model),
            'me_unread_count' => $me_unread_count,
            'me_process_count' => $me_process_count,
            'me_complete_count' => $me_complete_count,
            'reply_fail_count' => $reply_fail_count,
            'user_accounts' => $user_accounts,
        ];
        return view($this->viewPath . 'index', $response);
    }
}
