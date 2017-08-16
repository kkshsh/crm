@extends('layouts.default')
@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">@section('detailTitle') {{ $metas['title'] }} @show{{-- 详情标题 --}}</div>
        <div class="panel-body">
            @section('detailBody') @show{{-- 详情内容 --}}
        </div>
    </div>
@stop