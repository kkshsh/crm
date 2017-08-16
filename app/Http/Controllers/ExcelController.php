<?php
/**
 * Created by PhpStorm.
 * User: Vincent
 * Date: 16/1/8
 * Time: 上午9:19
 */

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\MessageModel;
use App\Models\UserModel;
use App\Models\Message\Message_logModel;
use App\Models\Message\Template\TypeModel;
use Excel;

class ExcelController extends Controller
{
    public function __construct(UserModel $user)
    {
    	$this->model = $user;
        $this->mainIndex = route('Excel.index');
        $this->mainTitle = '报表';
        $this->viewPath = 'excel.';
    }


    public function index()
    {
        request()->flash();
		$userarr=array(6,7,10,15,14,16,17,19,20,21);
		$users=UserModel::whereIn('id', $userarr)->get();
        $types=TypeModel::where('parent_id','<>', 0)->get();
        $response = [
        	'metas' => $this->metas(__FUNCTION__),
			'data' => $this->autoList($this->model->whereIn('id', $userarr)),
            'users' => $users,
            'types' => $types,
        ];
        return view($this->viewPath . 'index', $response);
    }


    //导表
    public function export()
    {
    	$start_time=request()->input('start_time');
    	$end_time=request()->input('end_time');
    	$messages=MessageModel::where('created_at','>',$start_time)->where('created_at','<=',$end_time)->get()->toArray();
    	if($start_time && $end_time) {
			$userarr=array(6,7,10,15,14,16,19,20,21);
			$types = TypeModel::where('parent_id', '<>', 0)->get();
			$users=UserModel::whereIn('id', $userarr)->get();
			//$name = "统计表格";
			//$rows = array();
			header('Content-Type: application/vnd.ms-excel charset=utf-8');
			header('Content-Disposition: attachment; filename="统计表.csv"');
			echo "name,回复总数,产品咨询,支付问题,验证问题,账户操作,追踪-未发货,追踪-已发送,追踪异常/异常件,尺码问题,质量问题,报等报缺,错发漏发,订单修改,客户选择退货,图货不一,其他咨询,网站问题,物流跟踪,用户追评,其他邮件类型,转交邮件数,转发邮件数\n";
			foreach ($users as $key => $value) {
				echo $value->name, ',';
				echo $value->getmessage($start_time, $end_time), ',';
				foreach ($types as $k => $v) {
					echo $value->getmessage1($v->id, $start_time, $end_time), ',';
				}
				echo $value->getmessage2($start_time, $end_time), ',';
				echo $value->getmessage3($value->name, $start_time, $end_time), ',';
				echo $value->getmessage4($value->id, $start_time, $end_time), ',', PHP_EOL;
			}
			exit;
		}
    }

}