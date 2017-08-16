@extends('common.form')
@section('formAction') {{ route('user.store') }} @stop
@section('formBody')
    <div class="row">
        <div class="form-group col-lg-4">
            <label for="group" class='control-label'>职位</label>
            <small class="text-danger glyphicon glyphicon-asterisk"></small>
            <select class="form-control" name="group" onchange="return changeGroup($(this));">
                @foreach(config('user.group') as $key=>$value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-lg-4">
            <label for="parent" class='control-label'>上级</label>
            <small class="text-danger glyphicon glyphicon-asterisk"></small>
            <select class="form-control" name="parent_id" id="parentId">
                <option value="0">请选择</option>
                @foreach($parents as $parent)
                    <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-lg-4">
            <label for="name" class='control-label'>姓名</label>
            <small class="text-danger glyphicon glyphicon-asterisk"></small>
            <input type='text' class="form-control" id="name" placeholder="用户姓名" name='name' value="{{ old('name') }}">
        </div>
    </div>
    <div class="row">
        <div class="form-group col-lg-6">
            <label for="email" class='control-label'>邮箱</label>
            <small class="text-danger glyphicon glyphicon-asterisk"></small>
            <input type='text' class="form-control" id="email" placeholder="用户邮箱" name='email' value="{{ old('email') }}">
        </div>
        <div class="form-group col-lg-6">
            <label for="password" class='control-label'>密码</label>
            <small class="text-danger glyphicon glyphicon-asterisk"></small>
            <input type='password' class="form-control" id="password" placeholder="用户密码" name='password' value="{{ old('password') }}">
        </div>
        <div class="form-group col-lg-4">
            <label for="group" class='control-label'>是否可登陆</label>
            <small class="text-danger glyphicon glyphicon-asterisk"></small>
            <select class="form-control" name="is_login" onchange="return changeGroup($(this));">
                @foreach(config('user.is_login') as $key=>$value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="row">
        <div class="panel panel-default">
            <div class="panel-heading">负责账号</div>
            <div class="panel-body">
                @foreach($accounts as $account)
                <div class="row form-group">
                    <div class="col-lg-2">
                        <input type="checkbox" name="accounts[]" value="{{ $account->id }}" />
                        <strong>账号</strong>: {{ $account->account }}
                    </div>
                    <div class="col-lg-4">
                        <strong>所属渠道</strong>: {{ $account->channel ? $account->channel->name : '' }}
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