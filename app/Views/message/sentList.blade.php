@extends('common.table')
@section('tableToolButtons')@stop
@section('tableHeader')
    <th class="sort" data-field="id">ID</th>
    <th>账号</th>
    <th>主题</th>
    <th class="sort" data-field="from_name">发信人</th>
    <th class="sort" data-field="from">发信邮箱</th>
    <th class="sort" data-field="date">发信日期</th>
    <th class="sort" data-field="created_at">创建日期</th>
    <th class="sort" data-field="updated_at">更新日期</th>
    <th>操作</th>
@stop
@section('tableBody')
    @foreach($data as $message)
        <tr>
            <td>{{ $message->id }}</td>
            <td>{{ $message->account ? $message->account->name : '' }}</td>
            <td>
                {!! $message->label_text !!}
                @if($message->status=='UNREAD')
                    <strong>{{ $message->subject }}</strong>
                @else
                    {{ $message->subject }}
                @endif
            </td>
            <td>{{ $message->from_name }}</td>
            <td>{{ $message->from }}</td>
            <td>{{ date('Y-m-d H:i:s',strtotime($message->date)) }}</td>
            <td>{{ $message->created_at }}</td>
            <td>{{ $message->updated_at }}</td>
            <td>
                <a href="{{ route('message.show', ['id'=>$message->id]) }}" class="btn btn-info btn-xs">
                    <span class="glyphicon glyphicon-eye-open"></span> 查看
                </a>
            </td>
        </tr>
    @endforeach
@stop
