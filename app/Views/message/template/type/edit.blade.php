@extends('common.form')
@section('formAction') {{ route('messageTemplateType.update', ['id' => $model->id]) }} @stop
@section('formBody')
    <input type="hidden" name="_method" value="PUT"/>
    <div class="row">
        <div class="form-group col-lg-6">
            <label for="parent_id" class='control-label'>上级类型</label>
            <small class="text-danger glyphicon glyphicon-asterisk"></small>
            <select class="form-control" name="parent_id" {{ $model->parent_id == 0 ? 'disabled' : '' }}>
                @if($model->parent_id == 0)
                    <option>一级模版类型</option>
                @else
                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_id') ? (old('parent_id') == $parent->id ? 'selected' : '') : ($model->parent_id  == $parent->id ? 'selected' : '') }}>
                            {{ $parent->name }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>
        <div class="form-group col-lg-6">
            <label for="name" class='control-label'>名称</label>
            <small class="text-danger glyphicon glyphicon-asterisk"></small>
            <input type='text' class="form-control" id="name" placeholder="类型名称" name='name' value="{{ old('name') ? old('name') : $model->name }}">
        </div>
    </div>
@stop