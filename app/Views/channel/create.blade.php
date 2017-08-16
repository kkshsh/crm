@extends('common.form')
@section('formAction') {{ route('channel.store') }} @stop
@section('formBody')
    <div class="row">
        <div class="form-group col-lg-4">
            <label for="name" class='control-label'>渠道名称</label>
            <small class="text-danger glyphicon glyphicon-asterisk"></small>
            <input type='text' class="form-control" id="name" placeholder="渠道名称" name='name' value="{{ old('name') }}">
        </div>
        <div class="form-group col-lg-4">
            <label for="alias" class='control-label'>渠道别名</label>
            <input type='text' class="form-control" id="alias" placeholder="渠道别名" name='alias' value="{{ old('alias') }}">
        </div>
    </div>
    <div class="row">
        <div class="form-group col-lg-4">
            <label for="api_type" class='control-label'>API类型</label>
            <select class="form-control" name="api_type">
                @foreach(config('channel.api_type') as $key=>$value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-lg-4">
            <label for="is_active" class='control-label'>是否有效</label>
            <select class="form-control" name="is_active">
                @foreach(config('channel.is_active') as $key=>$value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>
    </div>
@stop
