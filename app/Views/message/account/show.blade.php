@extends('common.detail')
@section('detailBody')
    <div class="panel panel-default">
        <div class="panel-heading">基础信息</div>
        <div class="panel-body">
            <div class="col-lg-3">
                <strong>ID</strong>: {{ $account->id }}
            </div>
            <div class="col-lg-3">
                <strong>渠道</strong>: {{ $account->channel->name }}
            </div>
            <div class="col-lg-3">
                <strong>账号</strong>: {{ $account->account }}
            </div>
            <div class="col-lg-3">
                <strong>名称</strong>: {{ $account->title }}
            </div>
            <div class="col-lg-3">
                <strong>前缀</strong>: {{ $account->prefix }}
            </div>
            <div class="col-lg-3">
                <strong>国家</strong>: {{ $account->country }}
            </div>
            <div class="col-lg-3">
                <strong>币种</strong>: {{ $account->currency }}
            </div>
            <div class="col-lg-12">
                <strong>简介</strong>: {{ $account->brief }}
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">日志信息</div>
        <div class="panel-body">
            <div class="col-lg-6">
                <strong>创建时间</strong>: {{ $account->created_at }}
            </div>
            <div class="col-lg-6">
                <strong>更新时间</strong>: {{ $account->updated_at }}
            </div>
        </div>
    </div>
@stop