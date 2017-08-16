@extends('common.form')
@section('formAction') {{ route('messageReply.update', ['id' => $model->id]) }} @stop
@section('formBody')
    <input type="hidden" name="_method" value="PUT"/>
    <div class="row">
        <div class="col-lg-6 form-group">
            Message:
            <a href="{{ route('message.show', ['id'=>$model->message_id]) }}" target="_blank">
                #{{ $model->message_id }}
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <div class="form-group">
                <input type="text" class="form-control" name="to" value="{{ $model->to }}"/>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="form-group">
                <input type="text" class="form-control" name="to_email" value="{{ $model->to_email }}"/>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="form-group">
                <input type="text" class="form-control" name="title" value="{{ $model->title }}"/>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="form-group">
                <textarea class="form-control" id="templateContent" rows="16" name="content">{{ $model->content }}</textarea>
            </div>
        </div>
    </div>
@stop