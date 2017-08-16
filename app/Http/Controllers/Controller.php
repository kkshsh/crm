<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use DataList;

abstract class Controller extends BaseController
{

    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests;

    protected $model;
    protected $viewPath;
    protected $mainIndex;
    protected $mainTitle;

    public function metas($action, $title = null)
    {
        $metas = [
            'mainIndex' => $this->mainIndex,
            'mainTitle' => $this->mainTitle,
            'title' => $title ? $title : $this->mainTitle . config('setting.titles.' . $action),
        ];
        return $metas;
    }

    public function alert($type, $content)
    {
        $response = ['type' => $type, 'content' => $content];
        return view('common.alert', $response)->render();
    }

    public function autoList($model, $fields = ['*'], $pageSize = null)
    {
        $list = $model;
        if (request()->has('keywords')) {
            $keywords = request()->input('keywords');
            $searchFields = $this->model->searchFields;
            $list = $list->where(function ($query) use ($keywords, $searchFields) {
                foreach ($searchFields as $searchField) {
                    $query = $query->orWhere($searchField, 'like', '%' . trim($keywords) . '%');
                }
            });
        }
        //更多查询
        if (request()->has('mixedSearchFields')) {
            $relateds = request()->input('mixedSearchFields');
            foreach ($relateds as $type => $related) {
                switch ($type) {
                    case 'relatedSearchFields':
                        foreach ($related as $relation_ship => $name_arr) {
                            foreach ($name_arr as $k => $name) {
                                $name = trim($name);
                                if ($name != '') {
                                    $list = $list->whereHas($relation_ship, function ($query) use ($k, $name) {
                                        $query = $query->where($k, 'like', '%' . $name . '%');
                                    });
                                }
                            }
                        }
                        break;
                    case 'doubleRelatedSearchFields':
                        foreach ($related as $relation_ship1 => $value1) {
                            foreach ($value1 as $relation_ship2 => $value2) {
                                foreach ($value2 as $key => $name) {
                                    $name = trim($name);
                                    if ($name != '') {
                                        $list = $list->whereHas($relation_ship1,
                                            function ($query) use ($relation_ship2, $name, $key) {
                                                $query = $query->wherehas($relation_ship2,
                                                    function ($query1) use ($name, $key) {
                                                        $query1 = $query1->where($key, 'like', '%' . $name . '%');
                                                    });
                                            });
                                    }
                                }
                            }
                        }
                        break;
                    case 'doubleRelatedSelectedFields':
                        foreach ($related as $relation_ship1 => $value1) {
                            foreach ($value1 as $relation_ship2 => $value2) {
                                foreach ($value2 as $key => $name) {
                                    $name = trim($name);
                                    if ($name != '') {
                                        $list = $list->whereHas($relation_ship1,
                                            function ($query) use ($relation_ship2, $name, $key) {
                                                $query = $query->wherehas($relation_ship2,
                                                    function ($query1) use ($name, $key) {
                                                        $query1 = $query1->where($key, 'like', '%' . $name . '%');
                                                    });
                                            });
                                    }
                                }
                            }
                        }
                        break;
                    case 'filterFields':
                        foreach ($related as $key => $value3) {
                            $value3 = trim($value3);
                            if ($value3) {
                                $list = $list->where($key, 'like', '%' . $value3 . '%');
                            }
                        }
                        break;
                    case 'filterSelects':
                        foreach ($related as $key => $value2) {
                            $value2 = trim($value2);
                            if ($value2||$value2=='0') {
                                $list = $list->where($key, $value2);
                            }
                        }
                        break;
                    case 'selectRelatedSearchs':
                        foreach ($related as $relation_ship => $contents) {
                            foreach ($contents as $name => $single) {
                                $single = trim($single);
                                if ($single != '') {
                                    $list = $list->whereHas($relation_ship, function ($query) use ($name, $single) {
                                        $query = $query->where($name, $single);
                                    });
                                }
                            }
                        }
                        break;
                    case 'sectionSelect':
                        foreach ($related as $kind => $content) {
                            if(!empty($content['begin']) && !empty($content['end'])) {
                                $list = $list->whereBetween($kind, [str_replace('/', '-', trim($content['begin'])), str_replace('/', '-', trim($content['end']))]);
                            }
                            if(empty($content['begin']) && !empty($content['end'])) {
                                $list = $list->where($kind, '<', str_replace('/', '-', trim($content['end'])));
                            }
                            if(!empty($content['begin']) && empty($content['end'])) {
                                $list = $list->where($kind, '>', str_replace('/', '-', trim($content['begin'])));
                            }
                        }
                        break;
                    case 'sectionGanged':
                        foreach ($related as $kind => $content) {
                            if($kind == 'first') {
                                foreach ($content as $relation_ship1 => $value1) {
                                    foreach ($value1 as $relation_ship2 => $value2) {
                                        foreach ($value2 as $key => $name) {
                                            $name = trim($name);
                                            if ($name != '') {
                                                $list = $list->whereHas($relation_ship1,
                                                    function ($query) use ($relation_ship2, $name, $key) {
                                                        $query = $query->wherehas($relation_ship2,
                                                            function ($query1) use ($name, $key) {
                                                                $query1 = $query1->where($key, 'like', '%' . $name . '%');
                                                            });
                                                    });
                                            }
                                        }
                                    }
                                }
                            }
                            if($kind == 'second') {
                                foreach ($content as $key => $value2) {
                                    $value2 = trim($value2);
                                    if ($value2||$value2=='0') {
                                        $list = $list->where($key, $value2);
                                    }
                                }
                            }
                        }
                        break;

                    case 'sectionGangedDouble':
                        foreach ($related as $kind => $content) {
                            if($kind == 'first') {
                                foreach ($content as $relation_ship1 => $value1) {
                                    foreach ($value1 as $relation_ship2 => $value2) {
                                        foreach ($value2 as $key => $name) {
                                            $name = trim($name);
                                            if ($name != '') {
                                                $list = $list->whereHas($relation_ship1,
                                                    function ($query) use ($relation_ship2, $name, $key) {
                                                        $query = $query->wherehas($relation_ship2,
                                                            function ($query1) use ($name, $key) {
                                                                $query1 = $query1->where($key, 'like', '%' . $name . '%');
                                                            });
                                                    });
                                            }
                                        }
                                    }
                                }
                            }
                            if($kind == 'second') {
                                foreach ($content as $relation_ship1 => $value1) {
                                    foreach ($value1 as $key => $value2) {
                                        $value2 = trim($value2);
                                        if ($value2||$value2=='0') {
                                            $list = $list->whereHas($relation_ship1, function($query) use ($value2){
                                                $query->where('c_name', 'like', '%'.$value2.'%');
                                            });
                                        }
                                    }
                                }
                            }
                        }
                        break;
                }
            }
        }

        if (request()->has('filters')) {
            foreach (DataList::filtersDecode(request()->input('filters')) as $filter) {
                $list = $list->where($filter['field'], $filter['oprator'], $filter['value']);
            }
        }
        if (request()->has('sorts')) {
            foreach (DataList::sortsDecode(request()->input('sorts')) as $sort) {
                $list = $list->orderBy($sort['field'], $sort['direction']);
            }
        } else {
            $list = $list->orderBy('id', 'desc');
        }
        if (!$pageSize) {
            $pageSize = request()->has('pageSize') ? request()->input('pageSize') : config('setting.pageSize');
        }
        return $list->paginate($pageSize, $fields);
    }

    /**
     * 列表
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        request()->flash();
        $response = [
            'metas' => $this->metas(__FUNCTION__),
            'data' => $this->autoList($this->model),
        ];
        return view($this->viewPath . 'index', $response);
    }

    /**
     * 详情
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id)
    {
        $model = $this->model->find($id);
        //$sum=$this->model::all();
        
        $count = $this->model->where('from','=',$model->from)->where('status','=','UNREAD')->count();
        if (!$model) {
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', $this->mainTitle . '不存在.'));
        }

        // 获取关联order_ids
        $order_ids = array();
        if($model->related ==1)
        {
            $orders =\App\Models\Message\OrderModel::where('message_id', $model->id)->get();
            foreach ($orders as $key=>$value)
            {
                $order_ids[] = array(
                    "order_id" => $value['order_id'],
                    "msg_orders_id" => $value['id'],
                    "created_at" => $value['created_at']
                );
            }
        }

        $erp_apiOrderinfo = $this->getOrderInfo_erp($order_ids);

        $response = [
            'metas' => $this->metas(__FUNCTION__),
            'model' => $model,
            'count' => $count,
            'erp_apiOrderinfo' => $erp_apiOrderinfo
        ];
        return view($this->viewPath . 'show', $response)->with('count',$count);
    }

    // CRM对接ERP,根据order_id获取order相关信息
    public function getOrderInfo_erp($order_ids)
    {
        $result = array();
        foreach($order_ids as $key=>$order_id)
        {
            if($order_id['created_at'] > config('setting.erpData'))
            {
                $post_data = array();
                $post_data['order_id'] = $order_id['order_id'];
                $url = "http://erp.wxzeshang.com:8000/api/crm_get_order_info/";
                $ch = curl_init();
                curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT,20);
                curl_setopt ( $ch, CURLOPT_TIMEOUT,20);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                $json_order_info = curl_exec($ch);
                if (curl_errno($ch)) {
                    echo 'Curl error: ' . curl_error($ch);
                    return;
                }
                curl_close($ch);
                $order_info = json_decode($json_order_info, true);
                $order_info['msg_orders_id'] = $order_id['msg_orders_id'];
                $result[] = $order_info;
            }
        }
        return $result;
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
        $this->model->create(request()->all());
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
        $response = [
            'metas' => $this->metas(__FUNCTION__),
            'model' => $model,
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
        $content=request()->all();
        $content['content'] = nl2br($content['content']);
        $model->update($content);
        return redirect($this->mainIndex);
    }

    /**
     * 删除
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        $model = $this->model->find($id);
        if (!$model) {
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', $this->mainTitle . '不存在.'));
        }
        $model->destroy($id);
        return redirect($this->mainIndex);
    }

    //查询筛选的数据
    public function allList($model, $list = null, $fields = ['*'], $pageSize = null)
    {
        $list = $list ? $list : $model;
        if (request()->has('mixedSearchFields')) {
            $relateds = request()->input('mixedSearchFields');
            foreach ($relateds as $type => $related) {
                switch ($type) {
                    case 'relatedSearchFields':
                        foreach ($related as $relation_ship => $name_arr) {
                            foreach ($name_arr as $k => $name) {
                                $name = trim($name);
                                if ($name != '') {
                                    $list = $list->whereHas($relation_ship, function ($query) use ($k, $name) {
                                        $query = $query->where($k, 'like', '%' . $name . '%');
                                    });
                                }
                            }
                        }
                        break;
                    case 'doubleRelatedSearchFields':
                        foreach ($related as $relation_ship1 => $value1) {
                            foreach ($value1 as $relation_ship2 => $value2) {
                                foreach ($value2 as $key => $name) {
                                    $name = trim($name);
                                    if ($name != '') {
                                        $list = $list->whereHas($relation_ship1,
                                            function ($query) use ($relation_ship2, $name, $key) {
                                                $query = $query->wherehas($relation_ship2,
                                                    function ($query1) use ($name, $key) {
                                                        $query1 = $query1->where($key, 'like', '%' . $name . '%');
                                                    });
                                            });
                                    }
                                }
                            }
                        }
                        break;
                    case 'doubleRelatedSelectedFields':
                        foreach ($related as $relation_ship1 => $value1) {
                            foreach ($value1 as $relation_ship2 => $value2) {
                                foreach ($value2 as $key => $name) {
                                    $name = trim($name);
                                    if ($name != '') {
                                        $list = $list->whereHas($relation_ship1,
                                            function ($query) use ($relation_ship2, $name, $key) {
                                                $query = $query->wherehas($relation_ship2,
                                                    function ($query1) use ($name, $key) {
                                                        $query1 = $query1->where($key, 'like', '%' . $name . '%');
                                                    });
                                            });
                                    }
                                }
                            }
                        }
                        break;
                    case 'filterFields':
                        foreach ($related as $key => $value3) {
                            $value3 = trim($value3);
                            if ($value3) {
                                $list = $list->where($key, 'like', '%' . $value3 . '%');
                            }
                        }
                        break;
                    case 'filterSelects':
                        foreach ($related as $key => $value2) {
                            $value2 = trim($value2);
                            if ($value2||$value2=='0') {
                                $list = $list->where($key, $value2);
                            }
                        }
                        break;
                    case 'selectRelatedSearchs':
                        foreach ($related as $relation_ship => $contents) {
                            foreach ($contents as $name => $single) {
                                $single = trim($single);
                                if ($single != '') {
                                    $list = $list->whereHas($relation_ship, function ($query) use ($name, $single) {
                                        $query = $query->where($name, $single);
                                    });
                                }
                            }
                        }
                        break;
                    case 'sectionSelect':
                        foreach ($related as $kind => $content) {
                            if(!empty($content['begin']) && !empty($content['end'])) {
                                $list = $list->whereBetween($kind, [str_replace('/', '-', trim($content['begin'])), str_replace('/', '-', trim($content['end']))]);
                            }
                            if(empty($content['begin']) && !empty($content['end'])) {
                                $list = $list->where($kind, '<', str_replace('/', '-', trim($content['end'])));
                            }
                            if(!empty($content['begin']) && empty($content['end'])) {
                                $list = $list->where($kind, '>', str_replace('/', '-', trim($content['begin'])));
                            }
                        }
                        break;
                }
            }
        }
        return $list;
    }

}
