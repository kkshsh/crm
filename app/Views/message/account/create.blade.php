@extends('common.form')
@section('formAction') {{ route('channelAccount.store') }} @stop
@section('formBody')
    <div class="form-group col-lg-6">
        <label for="type" class='control-label'>渠道</label>
        <small class="text-danger glyphicon glyphicon-asterisk"></small>
        <select class="form-control" name="channel_id">
            @foreach($channels as $channel)
                <option value="{{ $channel->id }}" {{ old('channel_id') == $channel->id ? 'selected' : '' }}>{{ $channel->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-lg-6">
        <label for="account" class='control-label'>账户</label>
        <small class="text-danger glyphicon glyphicon-asterisk"></small>
        <input type='text' class="form-control" id="account" placeholder="渠道账户" name='account' value="{{ old('account') }}">
    </div>
    <div class="form-group col-lg-3">
        <label for="title" class='control-label'>名称</label>
        <small class="text-danger glyphicon glyphicon-asterisk"></small>
        <input type='text' class="form-control" id="title" placeholder="渠道名称" name='title' value="{{ old('title') }}">
    </div>
    <div class="form-group col-lg-3">
        <label for="prefix" class='control-label'>前缀</label>
        <small class="text-danger glyphicon glyphicon-asterisk"></small>
        <input type='text' class="form-control" id="prefix" placeholder="渠道前缀,用于SKU,订单等的区分" name='prefix' value="{{ old('prefix') }}">
    </div>
    <div class="form-group col-lg-3">
        <label for="country" class='control-label'>国家</label>
        <select class="form-control" name="country">
            @foreach(config('channel.countries') as $country)
                <option value="{{ $country }}" {{ old('country')==$country?'selected':'' }}>{{ $country }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-lg-3">
        <label for="currency" class='control-label'>币种</label>
        <select class="form-control" name="currency">
            @foreach(config('channel.currencies') as $currency)
                <option value="{{ $currency }}" {{ old('currency')==$currency?'selected':'' }}>{{ $currency }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-lg-12">
        <label for="brief" class='control-label'>描述</label>
        <small class="text-danger glyphicon glyphicon-asterisk"></small>
        <textarea class="form-control" rows="3" name="brief">{{ old('brief') }}</textarea>
    </div>
@stop