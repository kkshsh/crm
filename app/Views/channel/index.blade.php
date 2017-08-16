@extends('common.table')
@section('tableHeader')
    <th class="sort" data-field="id">ID</th>
    <th>渠道名称</th>
    <th>渠道别称</th>
    <th>API类型</th>
    <th>是否有效</th>
    <th class="sort" data-field="created_at">创建日期</th>
    <th class="sort" data-field="updated_at">更新日期</th>
    <th>操作</th>
@stop
@section('tableBody')
    @foreach($data as $channel)
        <tr>
            <td>{{ $channel->id }}</td>
            <td>{{ $channel->name }}</td>
            <td>{{ $channel->alias }}</td>
            <td>{{ $channel->api_type}}</td>
            <td>{{ $channel->is_active == 1 ? '有效':'无效'}}</td>
            <td>{{ $channel->created_at }}</td>
            <td>{{ $channel->updated_at }}</td>
            <td>
                <a href="{{ route('channel.edit', ['id'=>$channel->id]) }}" class="btn btn-warning btn-xs">
                    <span class="glyphicon glyphicon-pencil"></span> 编辑
                </a>
                <a href="javascript:" class="btn btn-danger btn-xs delete_item"
                   data-id="{{ $channel->id }}"
                   data-url="{{ route('channel.destroy', ['id' => $channel->id]) }}">
                    <span class="glyphicon glyphicon-trash"></span> 删除
                </a>
            </td>
        </tr>
    @endforeach
@stop
