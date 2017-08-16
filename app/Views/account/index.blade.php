@extends('common.table')
@section('tableHeader')
    <th class="sort" data-field="id">ID</th>
    <th>渠道</th>
    <th>账号</th>
    <th>名称</th>
    <th>已勾选的标签名称</th>
    <th>是否有效</th>
    <th class="sort" data-field="created_at">创建日期</th>
    <th class="sort" data-field="updated_at">更新日期</th>
    <th>操作</th>
@stop
@section('tableBody')
    @foreach($data as $account)
        <tr>
            <td>{{ $account->id }}</td>
            <td>{{ $account->channel ? $account->channel->name : '' }}</td>
            <td>{{ $account->account }}</td>
            <td>{{ $account->name }}</td>
            <td>
                @foreach($account->is_get_mail as $key=>$v)
                    <strong>{{$v->name ? $v->name: ''}}</strong>,
                    <br/>
                @endforeach
            </td>
            <td>{{ $account->is_active == 1 ? '有效':'无效'}}</td>
            <td>{{ $account->created_at }}</td>
            <td>{{ $account->updated_at }}</td>
            <td>
                <a href="{{ route('account.edit', ['id'=>$account->id]) }}" class="btn btn-warning btn-xs">
                    <span class="glyphicon glyphicon-pencil"></span> 编辑
                </a>
                <button class="btn btn-primary btn-xs"
                        data-toggle="modal"
                        data-target="#myModal{{ $account->id }}"
                        title="API设置">
                    <span class="glyphicon glyphicon-link"></span>
                </button>
                <a href="javascript:" class="btn btn-danger btn-xs delete_item"
                   data-id="{{ $account->id }}"
                   data-url="{{ route('account.destroy', ['id' => $account->id]) }}">
                    <span class="glyphicon glyphicon-trash"></span> 删除
                </a>
            </td>
        </tr>
            @include('account.api.'.$account->channel->api_type)

    @endforeach
@stop
