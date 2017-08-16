<?php
/**
 * Created by PhpStorm.
 * User: Vincent
 * Date: 16/1/27
 * Time: 上午9:19
 */

namespace App\Http\Controllers;

use App\Models\UserModel;
use App\Models\Message\AccountModel;
use App\Models\UserAccountModel;

class UserController extends Controller
{
    public function __construct(UserModel $user)
    {
        $this->model = $user;
        $this->mainIndex = route('user.index');
        $this->mainTitle = '用户';
        $this->viewPath = 'user.';
        if (!in_array(request()->user()->group, ['leader', 'super'])) {
            exit($this->alert('danger', '无权限'));
        }
    }

    /**
     * 新建
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $response = [
            'metas' => $this->metas(__FUNCTION__),
            'parents' => $this->model->where('group', 'leader')->get(),
            'accounts' => AccountModel::all(),
        ];
        return view($this->viewPath . 'create', $response);
    }

    /**
     * 存储
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store()
    {
        request()->flash();
        $this->validate(request(), $this->model->rules('create'));
        $data = request()->all();
        $data['password'] = bcrypt($data['password']);
        $user_id = $this->model->create($data);
        // 新增负责账号
        $accounts = request()->input('accounts');
        if (!empty($accounts)){
            foreach ($accounts as $account)
            {
                $data = array(
                    'user_id' => $user_id['id'],
                    'account_id' => $account,
                );
                UserAccountModel::create($data);
            }
        }
        return redirect($this->mainIndex);
    }

    /**
     * 编辑
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $model = $this->model->find($id);
        if (!$model) {
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', $this->mainTitle . '不存在.'));
        }
        # 该用户负责的账号
        $user_accounts = UserAccountModel::where('user_id',$id)->get();
        $user_accounts_arr = array();
        foreach ($user_accounts as $key=>$value)
        {
            $user_accounts_arr[$value['account_id']] = $value['account_id'];
        }
        # 所有账号
        $accounts = AccountModel::all();
        # 在所有账号中标记该用户负责的账号
        $accounts_res = array();
        foreach ($accounts as $key=>$value)
        {
            $sel_flag = '';
            if (array_key_exists($value['id'], $user_accounts_arr)){
                $sel_flag = 'checked';
            }
            $accounts_res[] = array(
                'id' =>  $value['id'],
                'account' => $value['account'],
                'channel_name' => $value['channel'] ? $value['channel']->name : '',
                'sel_flag' => $sel_flag
            );
        }
        $response = [
            'metas' => $this->metas(__FUNCTION__),
            'parents' => $this->model->where('group', 'leader')->get(),
            'model' => $model,
            'accounts_res' => $accounts_res,
        ];
        return view($this->viewPath . 'edit', $response);
    }

    /**
     * 更新
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
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
        if(strlen($data['password'])>=30){
            $data['password'] = $data['password'];
        }else{
            $data['password'] = bcrypt($data['password']);
        }
        $model->update($data);
        // 先删除该用户负责的账号
        $user_accounts = UserAccountModel::where('user_id',$id)->get();
        foreach ($user_accounts as $user_account)
        {
            $user_account_id = $user_account['id'];
            UserAccountModel::destroy($user_account_id);
        }
        // 新增负责账号
        $accounts = request()->input('accounts');
        if (!empty($accounts)){
            foreach ($accounts as $account)
            {
                $data = array(
                    'user_id' => $id,
                    'account_id' => $account,
                );
                UserAccountModel::create($data);
            }
        }

        return redirect($this->mainIndex);
    }

}