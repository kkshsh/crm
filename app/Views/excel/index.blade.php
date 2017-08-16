@extends('common.table')
@section('tableToolButtons')
@stop
@section('tableHeader')
    <div align="left">
        <form action="{{ route('Excel.export') }}" method="POST">
            <div style="padding: 0 15px;">
                <small><label style="width: 130px;">按时间导表</label></small>
                From:<input type="text" name="start_time"  class="datetimepicker">
                to:<input type="text" name="end_time"  class="datetimepicker">
                <input type="submit" value="按时间导出数据" class="ui-button" style="padding:0 .5em">
                (由于邮件类型是4月5号才更新的功能之前回复的数据都是没有的 如:导出20号  请选择 20到21号)
            </div>
        </form>
        <form action="{{ route('messageorder.export') }}" method="POST">
            <div style="padding: 0 15px;">
                <small><label style="width: 130px;">按时间导投诉类型表</label></small>
                From:<input type="text" name="start_time"  class="datetimepicker">
                to:<input type="text" name="end_time"  class="datetimepicker">
                <input type="submit" value="按时间导出数据" class="ui-button" style="padding:0 .5em">

            </div>
        </form>
    </div>
    <th>id</th>
    <th class="sort" data-field="id">姓名(写在配置文件里面了如果要添加新客服user.staff 配置文件 id)</th>
    <th>回复总数</th>
    @foreach($types as $type)
    <th>{{ $type->name }}</th>
    @endforeach
    <th>其他邮件类型</th>
    <th>转交邮件数</th>
    <th>转发邮件数</th>
@stop
@section('tableBody')
    @foreach($users as $user)
        <tr>
            <td>{{ $user->id }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->process_mess }}</td>
            @foreach($types as $type)
            <td>{{ $user->ProcessType($type->id) }}</td>
            @endforeach
            <td>{{ $user->Process_Order }}</td>
            <td>{{ $user->MessageFor($user->name) }}</td>
            <td>{{ $user->MessageLog($user->id) }}</td>
        </tr>
    @endforeach
@stop
