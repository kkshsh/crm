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
    <th>message_id</th>
    <th>email</th>
    <th>解决方案</th>
    <th>退款金额</th>
    <th>订单号</th>
    <th>Package</th>
    <th>sku</th>
    <th>price</th>
    <th>qty</th>
    <th>投诉类型</th>
    <th>投诉详情</th>
    <th>具体描述</th>
    <th>创建时间</th>
    <th>处理人</th>
@stop
@section('tableBody')
    @foreach($data as $reply)
        <tr>            
            <td>{{ $reply->id }}</td>     
            <td>{{ $reply->message_id }}</td>
            @foreach($reply->assigner1 as $k=>$v)
            <td>{{ $v->email }}</td>
            <td>{{ $v->settled_name }}</td>
            <td>{{ $v->refund }}</td>
            @endforeach
            <td>{{ $reply->ordernum }}</td>
            <td>{{ $reply->packageid }}</td>
            <td>{{ $reply->sku }}</td>
            <td>{{ $reply->price }}</td>
            <td>{{ $reply->qty }}</td>
            <td>{{ $reply->com }}</td>
            <td>{{ $reply->com_name }}</td>
            <td>{{ $reply->content }}</td>
            <td>{{ $reply->created_at  }}</td>
            <td>{{ $reply->assigner?$reply->assigner->name:"" }}</td>
        </tr>
    @endforeach
@stop
