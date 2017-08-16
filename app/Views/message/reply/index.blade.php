@extends('common.table')
@section('tableToolButtons')
    <div class="btn-group">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="glyphicon glyphicon-filter"></i> 过滤
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li><a href="{{ DataList::filtersEncode(['status','=','NEW']) }}">待发</a></li>
                <li><a href="{{ DataList::filtersEncode(['status','=','SENT']) }}">已发</a></li>
                <li><a href="{{ DataList::filtersEncode(['status','=','FAIL']) }}">失败</a></li>
            </ul>
        </div>
    </div>
@stop
@section('tableHeader')
    <th class="sort" data-field="id">ID</th>
    <th>账号</th>
    <th>收信人</th>
    <th>收信邮箱</th>
    <th>状态</th>
    <th class="sort" data-field="created_at">创建时间</th>
    <th class="sort" data-field="updated_at">更新时间</th>
    <th>操作</th>
@stop
@section('tableBody')
    @foreach($data as $reply)
        <tr>
            <td>{{ $reply->id }}</td>
            <td>{{ $reply->message ? $reply->message->account->name : ''  }}</td>
            <td>{{ $reply->to }}</td>
            <td>{{ $reply->to_email }}</td>
            <td>{{ $reply->status }}</td>
            <td>{{ $reply->created_at }}</td>
            <td>{{ $reply->updated_at }}</td>
            <td>
                <a href="{{ route('messageReply.edit', ['id'=>$reply->id]) }}" class="btn btn-warning btn-xs">
                    <span class="glyphicon glyphicon-pencil"></span> 查看
                </a>
                @if($reply->status != 'SENT' && request()->user()->group == 'super')
                <a href="{{ route('messageReply.replysendmsg', ['id'=>$reply->id]) }}" class="btn btn-success btn-xs">
                    <span class="glyphicon glyphicon-pencil"></span> 发送
                </a>
                @endif
            </td>
        </tr>
    @endforeach
@stop
