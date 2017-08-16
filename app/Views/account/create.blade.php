@extends('common.form')
@section('formAction') {{ route('account.store') }} @stop
@section('formBody')
    <div class="row">
        <div class="form-group col-lg-2">
            <label for="channel_id" class='control-label'>渠道</label>
            <small class="text-danger glyphicon glyphicon-asterisk"></small>
            <select class="form-control" name="channel_id" id="channel_id">
                <option value="0">请选择</option>
                @foreach($channels as $channel)
                    <option value="{{ $channel->id }}">{{ $channel->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-lg-4">
            <label for="account" class='control-label'>账号</label>
            <small class="text-danger glyphicon glyphicon-asterisk"></small>
            <input type='text' class="form-control" id="account" placeholder="账号" name='account' value="{{ old('account') }}">
        </div>
        <div class="form-group col-lg-4">
            <label for="name" class='control-label'>名称</label>
            <small class="text-danger glyphicon glyphicon-asterisk"></small>
            <input type='text' class="form-control" id="name" placeholder="名称" name='name' value="{{ old('name') }}">
        </div>
        <div class="form-group col-lg-2">
            <label for="is_active" class='control-label'>是否有效</label>
            <select class="form-control" name="is_active">
                @foreach(config('channel.is_active') as $key=>$value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>
    </div>

@stop
