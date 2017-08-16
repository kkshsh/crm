@extends('common.form')
@section('formAction') {{ route('channel.update', ['id' => $model->id]) }} @stop
@section('formBody')
    <input type="hidden" name="_method" value="PUT"/>
    <div class="row">
        <div class="form-group col-lg-4">
            <label for="name" class='control-label'>渠道名称</label>
            <small class="text-danger glyphicon glyphicon-asterisk"></small>
            <input type='text' class="form-control" id="name" placeholder="渠道名称" name='name' value="{{ $model->name }}">
        </div>
        <div class="form-group col-lg-4">
            <label for="alias" class='control-label'>渠道别名</label>
            <input type='text' class="form-control" id="alias" placeholder="渠道别名" name='alias' value="{{ $model->alias }}">
        </div>
    </div>
    <div class="row">
        <div class="form-group col-lg-4">
            <label for="api_type" class='control-label'>API类型</label>
            <select class="form-control" name="api_type">
                @foreach(config('channel.api_type') as $key=>$value)
                    <option value="{{ $key }}" {{ $model->api_type == $value ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-lg-4">
            <label for="is_active" class='control-label'>是否有效</label>
            <select class="form-control" name="is_active">
                @foreach(config('channel.is_active') as $key=>$value)
                    <option value="{{ $key }}" {{ $model->is_active == $value ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
            </select>
        </div>
    </div>
@stop