@extends('common.form_no')
@section('formAction') {{ route('messageReply.update', ['id' => $model->id]) }} @stop
@section('formBody')
    <input type="hidden" name="_method" value="PUT"/>
    <div class="row">
        <div class="col-lg-6 form-group">
            Message:
                #{{ $model->id }}
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
    <div class="panel" style="height: 500px;">
        <div class="row">
            <div class="col-lg-8" style="height: 500px;">

                <div class="embed-responsive embed-responsive-16by9">
                    <?= $model->content ?>
                </div>
                展示代码:
                <div class="embed-responsive embed-responsive-16by9">
                    <textarea style="margin: 0px; width: 1215px; height: 517px;"><?= $model->content ?></textarea>
                </div>
            </div>
        </div>
    </div>
@stop