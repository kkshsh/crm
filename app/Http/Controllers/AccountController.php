<?php
/**
 * Created by PhpStorm.
 * User: xuli
 * Date: 2017/1/06
 */
namespace App\Http\Controllers;

use App\Models\Message\AccountModel;
use App\Models\Message\ChannelModel;
use App\Models\AccountLabelModel;

class AccountController extends Controller
{
    public function __construct(AccountModel $account)
    {
        $this->model = $account;
        $this->mainIndex = route('account.index');
        $this->mainTitle = '账号';
        $this->viewPath = 'account.';
        if (!in_array(request()->user()->group, ['super'])) {
            exit($this->alert('danger', '无权限'));
        }
    }

    public function index()
    {
        request()->flash();
        $response = [
            'metas' => $this->metas(__FUNCTION__),
            'data' => $this->autoList($this->model),
            'mixedSearchFields' => $this->model->mixed_search,
        ];
        return view($this->viewPath . 'index', $response);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $response = [
            'metas' => $this->metas(__FUNCTION__),
            'channels' => ChannelModel::all(),
        ];
        return view($this->viewPath . 'create', $response);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        request()->flash();
        $this->validate(request(), $this->model->rules('create'));
        $this->model->create(request()->all());
        return redirect($this->mainIndex);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $model = $this->model->find($id);
        if (!$model) {
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', $this->mainTitle . '不存在.'));
        }
        # 查询api_type,用于是否显示勾选标签
        $api_type = $model->channel->api_type;
        # 查询该账号下的所有标签
        $accounts_labels = AccountLabelModel::where('account_id', $id)->get();
        # 记录标签是否用于抓取进系统
        $accounts_labels_res = array();
        foreach ($accounts_labels as $key=>$value)
        {
            $sel_flag = '';
            if ($value['is_get_mail'] == 'get')
            {
                $sel_flag = 'checked';
            }
            $accounts_labels_res[] = array(
                'id' =>  $value['id'],
                'label_id' => $value['label_id'],
                'name' => $value['name'],
                'sel_flag' => $sel_flag
            );
        }
        $response = [
            'metas' => $this->metas(__FUNCTION__),
            'channels' => ChannelModel::all(),
            'model' => $model,
            'accounts_labels' => $accounts_labels_res,
            'api_type' => $api_type,
        ];
        return view($this->viewPath . 'edit', $response);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $model = $this->model->find($id);
        if (!$model) {
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', $this->mainTitle . '不存在.'));
        }
        request()->flash();
        $this->validate(request(), $this->model->rules('update', $id));
        $data = request()->all();
        $model->update($data);
        # 先将该账号下的标签全部更新为unshow
        $account_label = AccountLabelModel::where('account_id', $id);
        $account_label_data['is_get_mail']='unget';
        $account_label->update($account_label_data);
        # 更新账号需要抓取的标签
        $account_labels = request()->input('account_labels');
        if (!empty($account_labels)){
            foreach ($account_labels as $account_label_id)
            {
                $account_label = AccountLabelModel::where('id', $account_label_id);
                $account_label_data['is_get_mail']='get';
                $account_label->update($account_label_data);
            }
        }

        return redirect($this->mainIndex);
    }

    public function updateApi($id)
    {
        $model = $this->model->find($id);
        if (!$model) {
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', $this->mainTitle . '不存在.'));
        }
        $data = request()->all();
        $model->update($data);
        return redirect($this->mainIndex)->with('alert', $this->alert('success', $model->alias . ' 设置API成功.'));
    }

}
