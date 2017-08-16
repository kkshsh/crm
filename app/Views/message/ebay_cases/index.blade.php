@extends('common.table')
@section('tableToolButtons')
    <div class="btn-group" role="group">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="glyphicon glyphicon-filter"></i>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            Case状态(Status)
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
            @foreach($status as $item)
                <li>
                    <a href="{{ DataList::filtersEncode(['status','=',$item->status]) }}">{{ $item->status }}</a>
                </li>
            @endforeach
        </ul>
    </div>
    <div class="btn-group" role="group">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="glyphicon glyphicon-filter"></i>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            Case类型(Type)
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
            @foreach($types as $item)
                <li>
                    <a href="{{ DataList::filtersEncode(['type','=',$item->type]) }}">{{ $item->type }}</a>
                </li>
            @endforeach
        </ul>
    </div>
@stop

@section('tableHeader')

    <th class="sort" data-field="id">ID</th>
    <th>CaseID</th>
    <th>标题</th>
    <th>status</th>
    <th>type</th>
    <th>买家ID</th>
    <th>卖家ID</th>
    <th>交易号</th>
    <th>创建时间</th>
    <th>操作</th>

@stop
@section('tableBody')
    @foreach($data as $case)
        <tr>
            <td>{{$case->id}}</td>
            <td>{{$case->case_id}}</td>
            <td>{{$case->item_title}}</td>
            <td>{{$case->status}}</td>
            <td>{{$case->type}}</td>
            <td>{{$case->buyer_id}}</td>
            <td>{{$case->seller_id}}</td>
            <td>{{$case->transaction_id}}</td>
            <td>{{$case->creation_date}}</td>
            <td>
                @if($case->process_status == 'UNREAD')
                    <a href="ebayCases/{{$case->id}}/edit" class="btn btn-primary btn-xs">
                        <span class="glyphicon glyphicon-play"></span> 开始处理
                    </a>
                @endif
                @if($case->process_status == 'PROCESS')
                    <a href="ebayCases/{{$case->id}}/edit" class="btn btn-warning btn-xs">
                        <span class="glyphicon glyphicon-pause"></span> 继续处理
                    </a>
                @endif
                @if($case->process_status == 'COMPLETE')
                    <a href="" class="btn btn-info btn-xs">
                        <span class="glyphicon glyphicon-eye-open"></span> 已处理
                    </a>
                @endif
            </td>
        </tr>
    @endforeach

@stop
