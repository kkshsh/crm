@extends('common.detail')
@section('detailBody')
    <div class="panel panel-default">
        <div class="panel-heading">基础信息</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-lg-2">
                    <strong>ID</strong>: {{ $model->id }}
                </div>
                <div class="col-lg-4">
                    <strong>类型</strong>: {{ $model->type->name }}
                </div>
                <div class="col-lg-6">
                    <strong>名称</strong>: {{ $model->name }}
                </div>
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">模版</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-lg-12">
                    <p>{!! nl2br($model->content) !!}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">日志信息</div>
        <div class="panel-body">
            <div class="col-lg-6">
                <strong>创建时间</strong>: {{ $model->created_at }}
            </div>
            <div class="col-lg-6">
                <strong>更新时间</strong>: {{ $model->updated_at }}
            </div>
        </div>
    </div>
@stop