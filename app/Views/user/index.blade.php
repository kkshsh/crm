@extends('common.table')
@section('tableHeader')
    <th class="sort" data-field="id">ID</th>
    <th>职位</th>
    <th>上级</th>
    <th>名称</th>
    <th>账号</th>
    <th>所负责渠道账号</th>
    <th class="sort" data-field="created_at">创建日期</th>
    <th class="sort" data-field="updated_at">更新日期</th>
    <th>操作</th>
@stop
@section('tableBody')
    @foreach($data as $user)
        <tr>
            <td>{{ $user->id }}</td>
            <td>{{ $user->group_text }}</td>
            <td>{{ $user->parent ? $user->parent->name : '' }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>
                @foreach($user->accounts as $key=>$v)
                    <span class="text-success">渠道账号</span>:<strong>{{$v->account_name ? $v->account_name->account: ''}}</strong>
                    [<span class="text-success">渠道</span>:{{$v->account_name ? $v->account_name->channel->name: ''}}],
                    <br/>
                @endforeach
            </td>
            <td>{{ $user->created_at }}</td>
            <td>{{ $user->updated_at }}</td>
            <td>
                <a href="{{ route('user.edit', ['id'=>$user->id]) }}" class="btn btn-warning btn-xs">
                    <span class="glyphicon glyphicon-pencil"></span> 编辑
                </a>
                <a href="javascript:" class="btn btn-danger btn-xs delete_item"
                   data-id="{{ $user->id }}"
                   data-url="{{ route('user.destroy', ['id' => $user->id]) }}">
                    <span class="glyphicon glyphicon-trash"></span> 删除
                </a>
                @if($user->is_login == 1)
                    <button type="" class="">
                        <span class="glyphicon glyphicon-ok"></span> 有效
                    </button>
                @else
                    <button type="" class="">
                        <span class="glyphicon glyphicon-remove"></span> 无效
                    </button>
                @endif
            </td>
        </tr>
    @endforeach
@stop
