<?php
/**
 * Created by PhpStorm.
 * User: xuli
 * Date: 2017/1/06
 */
namespace App\Http\Controllers;

use App\Models\Message\ChannelModel;

class ChannelController extends Controller
{
    public function __construct(ChannelModel $channel)
    {
        $this->model = $channel;
        $this->mainIndex = route('channel.index');
        $this->mainTitle = '渠道';
        $this->viewPath = 'channel.';
        if (!in_array(request()->user()->group, ['super'])) {
            exit($this->alert('danger', '无权限'));
        }
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
        $response = [
            'metas' => $this->metas(__FUNCTION__),
            'model' => $model,
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
        return redirect($this->mainIndex);
    }

}
