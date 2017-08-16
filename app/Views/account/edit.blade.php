@extends('common.form')
@section('formAction') {{ route('account.update', ['id' => $model->id]) }} @stop
@section('formBody')
    <input type="hidden" name="_method" value="PUT"/>
    <div class="row">
        <div class="form-group col-lg-2">
            <label for="channel_id" class='control-label'>渠道</label>
            <small class="text-danger glyphicon glyphicon-asterisk"></small>
            <select class="form-control" name="channel_id" id="channel_id">
                <option value="0">请选择</option>
                @foreach($channels as $channel)
                    <option value="{{ $channel->id }}" {{ $model->channel_id == $channel->id ? 'selected' : '' }}>{{ $channel->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-lg-4">
            <label for="account" class='control-label'>账号</label>
            <small class="text-danger glyphicon glyphicon-asterisk"></small>
            <input type='text' class="form-control" id="account" placeholder="账号" name='account' value="{{ $model->account }}">
        </div>
        <div class="form-group col-lg-4">
            <label for="name" class='control-label'>名称</label>
            <small class="text-danger glyphicon glyphicon-asterisk"></small>
            <input type='text' class="form-control" id="name" placeholder="名称" name='name' value="{{ $model->name }}">
        </div>
        <div class="form-group col-lg-2">
            <label for="is_active" class='control-label'>是否有效</label>
            <select class="form-control" name="is_active">
                @foreach(config('channel.is_active') as $key=>$value)
                    <option value="{{ $key }}" {{ $model->is_active == $value ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
            </select>
        </div>
    </div>
<?php
if(isset($api_type) && $api_type == 'amazon')
  {
?>
    <div class="row">
        <div class="panel panel-default">
            <div class="panel-heading">抓取邮件的标签[勾选表示需要抓进系统]</div>
            <div class="panel-body">
                @foreach($accounts_labels as $key=>$value)
                    <div class="row form-group">
                        <div class="col-lg-4">
                            <input type="checkbox" {{$value['sel_flag'] ? "checked" : ''}} name="account_labels[]" value="{{ $value['id'] }}" />
                            <strong>标签名称</strong>: {{ $value['name'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <?php
    }
   ?>
@stop