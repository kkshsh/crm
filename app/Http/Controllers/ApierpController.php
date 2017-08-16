<?php
/**
 * Created by PhpStorm.
 * User: xuli
 * Date: 16/11/24
 * Time: 上午11:00
 */

namespace App\Http\Controllers;
use App\Models\MessageModel;
use DB;
use App\Models\UserModel;
use App\Models\Message\MessageOrderModel;
use App\Models\Message\MessageComplaintModel;

class ApierpController extends Controller
{
    # API 返回当天，昨天，前7天的回复邮件总数
    public function api_get_replay_count()
    {
        $param = request()->all();
        if (isset($param) && $param == ["{\"replay\": \"All\"}"]) {
            $result_arr = array();
            $userarr = array(6, 7, 10, 15, 14, 16, 18, 19, 20, 21, 22, 36, 37, 39, 41, 42, 43);
            $users = UserModel::whereIn('id', $userarr)->get();
            $time = time();
            $time_today = date("Y-m-d", $time);
            $time_tomorrow = date("Y-m-d", strtotime("+1 day"));
            $time_yesterday = date("Y-m-d", strtotime("-1 day"));
            $time_last_week = date("Y-m-d", strtotime("-7 day"));
            $today_replay_sum = 0;
            $yesterday_replay_sum = 0;
            $last_week_replay_sum = 0;
            foreach ($users as $key => $value) {
                $today_replay = $value->getmessage($time_today, $time_tomorrow);
                $yesterday_replay = $value->getmessage($time_yesterday, $time_today);
                $last_week_replay = $value->getmessage($time_last_week, $time_today);

                $today_replay_sum = $today_replay_sum + $today_replay;
                $yesterday_replay_sum = $yesterday_replay_sum + $yesterday_replay;
                $last_week_replay_sum = $last_week_replay_sum + $last_week_replay;
            }
            $result_arr[] = array(
                'today_replay_sum' => $today_replay_sum,
                'yesterday_replay_sum' => $yesterday_replay_sum,
                'last_week_replay_sum' => $last_week_replay_sum
            );

            $result_json = response()->json(["today_replay_sum" => $today_replay_sum,
                "yesterday_replay_sum" => $yesterday_replay_sum,
                "last_week_replay_sum" => $last_week_replay_sum]);
            return $result_json;
        } else {
            return response()->json(["message" => 'Failed']);
            exit;
        }
    }

    # API 返回未读邮件数，未处理邮件数
    public function api_get_msg_count(){
        $param = request()->all();
        if (isset($param) && $param == ["{\"message\": \"Count\"}"]) {
            $unread_msg = MessageModel::where('status', 'UNREAD')->count();
            $process_msg = MessageModel::where('status', 'PROCESS')->count();

            $result_json = response()->json(["unread_msg" => $unread_msg,
                "process_msg" => $process_msg]);
            return $result_json;
        } else {
            return response()->json(["message" => 'Failed']);
            exit;
        }
    }

    # API 返回客服的投诉和退款信息(前三天)
    # 订单号，退款金额，退款方式, sku，qty(投诉数量), 投诉类型，投诉原因，投诉备注, 包裹号
    public function api_get_complaint(){
        $param = request()->all();
        if (isset($param) && $param == ["{\"complaint\": \"3\"}"]) {
            $time_threeday = date("Y-m-d", strtotime("-3 day"));

            $message_complaint = MessageComplaintModel::where('created_at', '>', $time_threeday)->get();
            $result_arr = array();
            foreach ($message_complaint as $value) {
                # 订单号
                $ordernum = $value->ordernum;
                # 创建时间
                $complaint_time = date("Y-m-d H:i:s", strtotime($value->created_at));
                # 退款总金额
                $refund = $value->refund ? $value->refund : "";
                # 退款方式
                $settled_name = $value->settled_name ? $value->settled_name : "";
                # 处理人
                $settled_user = '';
                $ws_return_arr = explode('处理人:',$value->ws_return);
                if (isset($ws_return_arr) and isset($ws_return_arr[1]) and count($ws_return_arr)==2) {
                    $settled_user = $ws_return_arr[1];
                }

                # sku详细信息
                $assigner1 = $value->assigner1;
                $sku_details = array();
                foreach ($assigner1 as $sku_detail) {
                    # sku
                    $sku = $sku_detail->sku;
                    # 数量
                    $qty = $sku_detail->qty;
                    # 投诉类型
                    $com = $sku_detail->com;
                    # 投诉原因
                    $com_name = $sku_detail->com_name;
                    # 投诉备注
                    $content = $sku_detail->content ? $sku_detail->content : "(无)";
                    # 包裹号
                    $packageid = $sku_detail->packageid ? $sku_detail->packageid : "";
                    # 退款金额（针对单个sku）
                    $refund_amount = $sku_detail->refund_amount ? $sku_detail->refund_amount : "";

                    $sku_details[] = array(
                        "sku" => $sku,
                        "qty" => $qty,
                        "com" => $com,
                        "com_name" => $com_name,
                        "content" => $content,
                        "packageid" => $packageid,
                        "refund_amount" => $refund_amount);
                }
                $result_arr[] = array(
                    "ordernum" => $ordernum,
                    "complaint_time" => $complaint_time,
                    "refund" => $refund,
                    "settled_name" => $settled_name,
                    "sku_details" => $sku_details,
                    "settled_user" => $settled_user);
            }
            $result_json = response()->json($result_arr);
            return $result_json;

        }else {
            return response()->json(["message" => 'Failed']);
            exit;
        }
    }
}