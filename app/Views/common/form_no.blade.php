@extends('layouts.default')
@section('content')
    @if (count($errors) > 0)
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="panel panel-default">
        <div class="panel-heading">@section('formTitle') {{ $metas['title'] }} @show{{-- 表单标题 --}}</div>
        <div class="panel-body">
            @section('formBody')@show{{-- 表单内容 --}}
            @section('formButton')
                <div class="row">
                    <div class="col-lg-12">
                    </div>
                </div>
            @show{{-- 表单按钮 --}}
        </div>
    </div>
@stop