@extends('common.form')
@section('formAction') {{ route('user.update', ['id' => $model->id]) }} @stop
@section('formBody')
    <input type="hidden" name="_method" value="PUT"/>
    <div class="row">
        <div class="form-group col-lg-4">
            <label for="group" class='control-label'>职位</label>
            <small class="text-danger glyphicon glyphicon-asterisk"></small>
            <select class="form-control" name="group" onchange="return changeGroup($(this));">
                @foreach(config('user.group') as $key=>$value)
                    <option value="{{ $key }}" {{ $model->group == $key ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-lg-4">
            <label for="parent" class='control-label'>上级</label>
            <small class="text-danger glyphicon glyphicon-asterisk"></small>
            <select class="form-control" name="parent_id" id="parentId" style="display:{{ $model->group == 'leader' ? 'none' : 'block' }}">
                <option value="0">请选择</option>
                @foreach($parents as $parent)
                    <option value="{{ $parent->id }}" {{ $model->parent_id == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-lg-4">
            <label for="name" class='control-label'>姓名</label>
            <small class="text-danger glyphicon glyphicon-asterisk"></small>
            <input type='text' class="form-control" id="name" placeholder="用户姓名" name='name' value="{{ $model->name }}">
        </div>
    </div>
    <div class="row">
        <div class="form-group col-lg-6">
            <label for="email" class='control-label'>邮箱</label>
            <small class="text-danger glyphicon glyphicon-asterisk"></small>
            <input type='text' class="form-control" id="email" placeholder="用户邮箱" name='email' value="{{ $model->email }}">
        </div>
        <div class="form-group col-lg-6">
            <label for="password" class='control-label'>密码</label>
            <small class="text-danger glyphicon glyphicon-asterisk"></small>
            <input type='password' class="form-control" id="password" placeholder="用户密码" name='password' value="{{ $model->password }}">
        </div>
        <div class="form-group col-lg-4">
            <label for="group" class='control-label'>是否可登陆</label>
            <small class="text-danger glyphicon glyphicon-asterisk"></small>
            <select class="form-control" name="is_login" onchange="return changeGroup($(this));">
                @foreach(config('user.is_login') as $key=>$value)
                    @if($model->is_login==$key)
                        <option value="{{ $key }}" selected="selected" >{{ $value }}</option>
                    @else
                        <option value="{{ $key }}" >{{ $value }}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>
    <div class="row">
        <div class="panel panel-default">
            <div class="panel-heading">负责账号</div>
            <div class="panel-body">
                @foreach($accounts_res as $key=>$value)
                    <div class="row form-group">
                        <div class="col-lg-2">
                            <input type="checkbox" {{$value['sel_flag'] ? "checked" : ''}} name="accounts[]" value="{{ $value['id'] }}" />
                            <strong>账号</strong>: {{ $value['account'] }}
                        </div>
                        <div class="col-lg-4">
                            <strong>所属渠道</strong>: {{ $value['channel_name'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@stop
@section('pageJs')
    <script type="text/javascript">
        function changeGroup(group) {
            if ($(group).val() == 'leader') {
                $('#parentId').val('0');
                $('#parentId').hide();
            }
            if ($(group).val() == 'staff') {
                $('#parentId').show();
            }
        }
    </script>
@stop