<?php
/**
 * 信息模版控制器
 *
 * 2016-01-14
 * @author: Vincent<nyewon@gmail.com>
 */

namespace App\Http\Controllers\Message;

use App\Http\Controllers\Controller;
use App\Models\Message\TemplateModel;
use App\Models\Message\Template\TypeModel;
use App\Models\UserModel;

class TemplateController extends Controller
{
    public function __construct(TemplateModel $template)
    {
        $this->model = $template;
        $this->mainIndex = route('messageTemplate.index');
        $this->mainTitle = '信息模版';
        $this->viewPath = 'message.template.';
    }

    public function index()
    {
        request()->flash();
        $userarr=config('user.staff');
        $users=UserModel::whereIn('id', $userarr)->get();
        $response = [
            'metas' => $this->metas(__FUNCTION__),
            'data' => $this->autoList($this->model),
            'users' => $users,
            'mixedSearchFields' => $this->model->mixed_search,
        ];
        return view($this->viewPath . 'index', $response);
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
            'parents' => TypeModel::where('parent_id', 0)->get(),
        ];
        return view($this->viewPath . 'create', $response);
    }


    //展示
    public function show($id)
    {
        $type = $this->model->find($id);

        if (!$type) {
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', $this->mainTitle . '不存在.'));
        }
        $response = [
            'metas' => $this->metas(__FUNCTION__),
            'parents' => TypeModel::where('parent_id', 0)->get(),
            'model' => $type,
        ];
        return view($this->viewPath . 'show', $response);
    }
    /**
     * 编辑
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $template = $this->model->find($id);
        if (!$template) {
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', $this->mainTitle . '不存在.'));
        }
        $response = [
            'metas' => $this->metas(__FUNCTION__),
            'parents' => TypeModel::where('parent_id', 0)->get(),
            'model' => $template,
        ];
        return view($this->viewPath . 'edit', $response);
    }

    /**
     * AJAX 获取信息模版
     *
     * @return string
     */
    public function ajaxGetTemplate()
    {
        if (request()->ajax()) {
            $template = $this->model->find(request()->input('id'));
            if ($template) {
                $response = $template->toJson();
                return $response;
            }
        }
        return 'error';
    }
}