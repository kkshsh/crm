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
    <th>订单号</th>
    <th>邮箱</th>
    <th>解决方案</th>
    <th>退款金额</th>
    <th>创建时间</th>
    <th>sku详情</th>
    <th>操作</th>
@stop
@section('tableBody')
    @foreach($data as $reply)
        <tr>            
            <td>{{ $reply->id }}</td>      
            <td>{{ $reply->message_id }}</td>
            <td>{{ $reply->ordernum }}</td>
            <td>{{ $reply->email }}</td>
            <td>{{ $reply->settled_name }}</td>
            <td>{{ $reply->refund }}</td>
            <td>{{ date("Y-m-d",$reply->created)  }}</td>
            <td>
                @foreach($reply->assigner1 as $key=>$v)
                    <span class="text-success">sku</span>:<strong>{{$v->sku}}</strong>,
                    <span class="text-success">price</span>:<strong>{{$v->price}}</strong>,
                    <span class="text-success">qty</span>:<strong>{{$v->qty}}</strong>,
                    <span class="text-success">投诉类型</span>:<strong>{{$v->com}}</strong>,
                    <span class="text-success">投诉详情</span>:<strong>{{$v->com_name}}</strong>,
                    <span class="text-success">具体描述</span>:<strong>{{$v->content?$v->content:"(无)"}}</strong>,
                    <span class="text-success">packageid</span>:<strong>{{$v->packageid?$v->packageid:"--"}}</strong>,
                    <span class="text-success">退款金额</span>:<strong>{{$v->refund_amount?$v->refund_amount:"--"}}</strong>;
                    <br/>
                  @endforeach 
            </td>
            <td>
                <a href="/sendemail/edit/{{$reply->id}}" class="btn btn-warning btn-xs">
                    <span class="glyphicon glyphicon-pencil"></span> 查看
                </a>
            </td>
        </tr>
    @endforeach
@stop
