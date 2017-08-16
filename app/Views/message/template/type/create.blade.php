@extends('common.form')
@section('formAction') {{ route('messageTemplateType.store') }} @stop
@section('formBody')
    <div class="row">
        <div class="form-group col-lg-6">
            <label for="parent_id" class='control-label'>上级类型</label>
            <small class="text-danger glyphicon glyphicon-asterisk"></small>
            <select class="form-control" name="parent_id">
                <option>一级模版类型</option>
                @foreach($parents as $parent)
                    <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-lg-6">
            <label for="name" class='control-label'>名称</label>
            <small class="text-danger glyphicon glyphicon-asterisk"></small>
            <input type='text' class="form-control" id="name" placeholder="类型名称" name='name' value="{{ old('name') }}">
        </div>
    </div>
@stop