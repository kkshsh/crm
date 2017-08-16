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
    <th class="sort" data-field="created_at">创建时间</th>
    <th>message_id</th>
    <th>foruser</th>
    <th>assign_id</th>
    <th>touser</th>
    <th class="sort" data-field="updated_at">更新时间</th>
@stop
@section('tableBody')
    @foreach($data as $reply)
        <tr>
            <td>{{ $reply->id }}</td>
            <td>{{ $reply->created_at }}</td>
            <td>{{ $reply->message_id }}</td>
            <td>{{ $reply->foruser }}</td>
            <td>{{ $reply->assign_id }}</td>
            <td>{{ $reply->touser }}</td>
            <td>{{ $reply->updated_at }}</td>
        </tr>
    @endforeach
@stop
