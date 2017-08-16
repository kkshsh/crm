@extends('layouts.default')
@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">
            <strong>@section('tableTitle') {{ $metas['title'] }} @show{{-- 列表标题 --}}</strong>
        </div>
        <div class="panel-body">
            <div class="">
                @section('tableToolbar')
                    <div class="row toolbar">
                        <form action="" method="get">
                            <div class="col-lg-3">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="keywords" value="{{ old('keywords') }}" placeholder="查找..."/>
                            <span class="input-group-btn">
                                <button class="btn btn-default" type="submit">
                                    <i class="glyphicon glyphicon-search"></i>
                                </button>
                                <a class="btn btn-default" href="{{ request()->url() }}">
                                    <i class="glyphicon glyphicon-remove"></i>
                                </a>
                            </span>
                                </div>
                            </div>
                        </form>
                        <div class="text-right col-lg-9">
                            @section('tableToolButtons')
                                <div class="btn-group">
                                    <a class="btn btn-success" href="{{ route(request()->segment(1).'.create') }}">
                                        <i class="glyphicon glyphicon-plus"></i> 新增
                                    </a>
                                </div>
                            @show{{-- 工具按钮 --}}
                        </div>
                    </div>
                    {{--更多查询--}}
                    @if(isset($mixedSearchFields))
                        {{--<div class="col-lg-12">--}}
                        {{--<div class="collapse" id="collapseExample">--}}
                        <form action="" method="get">
                            <div class="searchDiv row">
                                @foreach($mixedSearchFields as $type => $value)
                                    @if($type == 'doubleRelatedSearchFields')
                                        @foreach($value as $relation_ship1 => $value1)
                                            @foreach($value1 as $relation_ship2 => $value2)
                                                @foreach($value2 as $key => $name)
                                                    <div class="col-lg-2 form-group searchItem">
                                                        <input type="text" class="form-control" value="{{request()->has('mixedSearchFields'.'.'.$type.'.'.$relation_ship1.'.'.$relation_ship2.'.'.$name)?request('mixedSearchFields'.'.'.$type.'.'.$relation_ship1.'.'.$relation_ship2.'.'.$name) : ''}}" name="mixedSearchFields[{{$type}}][{{ $relation_ship1 }}][{{ $relation_ship2 }}][{{ $name }}]" placeholder="{{ config('setting.transfer_search')[$relation_ship1.'.'.$relation_ship2.'.'.$name] }}"/>
                                                    </div>
                                                @endforeach
                                            @endforeach
                                        @endforeach
                                    @endif
                                    @if($type == 'doubleRelatedSelectedFields')
                                        @foreach($value as $relation_ship1 => $value1)
                                            @foreach($value1 as $relation_ship2 => $value2)
                                                @foreach($value2 as $key => $content)
                                                    <div class="col-lg-2 form-group searchItem">
                                                        <select name="mixedSearchFields[{{$type}}][{{ $relation_ship1 }}][{{ $relation_ship2 }}][{{ $key }}]" class='form-control select_select0 col-lg-2'>
                                                            <option value=''>{{config('setting.transfer_search')[$relation_ship1.'.'.$relation_ship2.'.'.$key]}}</option>
                                                            @foreach($content as $k => $v)
                                                                <option value="{{ $k }}" {{request()->has('mixedSearchFields'.'.'.$type.'.'.$relation_ship1.'.'.$relation_ship2.'.'.$key) ? ($k==request('mixedSearchFields'.'.'.$type.'.'.$relation_ship1.'.'.$relation_ship2.'.'.$key)?'selected':'') : ''}} >{{$v}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @endforeach
                                            @endforeach
                                        @endforeach
                                    @endif
                                    @if($type == 'relatedSearchFields')
                                        @if(count($value))
                                            @foreach($value as $relation_ship => $name_arr)
                                                @foreach($name_arr as $name)
                                                    <div class="col-lg-2 form-group searchItem">
                                                        <input type="text" value="{{request()->has('mixedSearchFields'.'.'.$type.'.'.$relation_ship.'.'.$name)?request('mixedSearchFields'.'.'.$type.'.'.$relation_ship.'.'.$name) : ''}}" class="form-control" name="mixedSearchFields[{{$type}}][{{ $relation_ship }}][{{ $name }}]" placeholder="{{ config('setting.transfer_search')[$relation_ship.'.'.$name] }}"/>
                                                    </div>
                                                @endforeach
                                            @endforeach
                                        @endif
                                    @endif
                                    @if($type == 'filterFields')
                                        @foreach($value as $name1)
                                            <div class="col-lg-2 form-group searchItem">
                                                <input type="text" value="{{request()->has('mixedSearchFields'.'.'.$type.'.'.$name1)?request('mixedSearchFields'.'.'.$type.'.'.$name1):''}}" class="form-control" name="mixedSearchFields[{{$type}}][{{ $name1 }}]" placeholder="{{ config('setting.transfer_search')[$name1] }}"/>
                                            </div>
                                        @endforeach
                                    @endif
                                    @if($type == 'filterSelects')
                                        @foreach($value as $name => $content)
                                            <div class="col-lg-2 form-group searchItem">
                                                <select name="mixedSearchFields[{{$type}}][{{ $name }}]" class='form-control select_select0 col-lg-2'>
                                                    <option value=''>{{config('setting.transfer_search')[$name]}}</option>
                                                    @foreach($content as $k => $v)
                                                        <option value="{{ $k }}" {{request()->has('mixedSearchFields'.'.'.$type.'.'.$name) ? ($k==request('mixedSearchFields'.'.'.$type.'.'.$name)?'selected':'') : ''}} >{{$v}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endforeach
                                    @endif
                                    @if($type == 'selectRelatedSearchs')
                                        @foreach($value as $relation_ship => $contents)
                                            @foreach($contents as $name => $single)
                                                <div class='col-lg-2 form-group searchItem'>
                                                    <select name="mixedSearchFields[{{$type}}][{{ $relation_ship }}][{{ $name }}]" class='form-control select_select0 col-lg-2'>
                                                        <option value=''>{{config('setting.transfer_search')[$relation_ship.'.'.$name]}}</option>
                                                        @foreach($single as $key => $value1)
                                                            <option value="{{ $key }}" {{request()->has('mixedSearchFields'.'.'.$type.'.'.$relation_ship.'.'.$name) ? ($key==request()->input('mixedSearchFields'.'.'.$type.'.'.$relation_ship.'.'.$name)?'selected':'') : '' }} >{{$value1}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endforeach
                                        @endforeach
                                    @endif
                                    @if($type == 'sectionSelect')
                                        @foreach($value as $kind => $contents)
                                            @foreach($contents as $content)
                                                @if($kind == 'time')
                                                    <div class='col-lg-2 form-group searchItem'>
                                                        <input type='text' value="{{request()->has('mixedSearchFields'.'.'.$type.'.'.$content.'.'.'begin')?request('mixedSearchFields'.'.'.$type.'.'.$content.'.'.'begin'):''}}" class='form-control datetime_select' name="mixedSearchFields[{{$type}}][{{$content}}][begin]" placeholder="起始{{config('setting.transfer_search')[$kind.'.'.$content]}}">
                                                    </div>
                                                    <div class='col-lg-2 form-group searchItem'>
                                                        <input type='text' value="{{request()->has('mixedSearchFields'.'.'.$type.'.'.$content.'.'.'end')?request()->input('mixedSearchFields'.'.'.$type.'.'.$content.'.'.'end'):''}}" class='form-control datetime_select' name="mixedSearchFields[{{$type}}][{{$content}}][end]" placeholder="结束{{config('setting.transfer_search')[$kind.'.'.$content]}}">
                                                    </div>
                                                @endif
                                                @if($kind == 'price')
                                                    <div class='col-lg-2 form-group searchItem'>
                                                        <input type='text' value="{{request()->has('mixedSearchFields'.'.'.$type.'.'.$content.'.'.'begin')?request('mixedSearchFields'.'.'.$type.'.'.$content.'.'.'begin'):''}}" class='form-control' name="mixedSearchFields[{{$type}}][{{$content}}][begin]" placeholder="起始{{config('setting.transfer_search')[$kind.'.'.$content]}}">
                                                    </div>
                                                    <div class='col-lg-2 form-group searchItem'>
                                                        <input type='text' value="{{request()->has('mixedSearchFields'.'.'.$type.'.'.$content.'.'.'end')?request()->input('mixedSearchFields'.'.'.$type.'.'.$content.'.'.'end'):''}}" class='form-control' name="mixedSearchFields[{{$type}}][{{$content}}][end]" placeholder="结束{{config('setting.transfer_search')[$kind.'.'.$content]}}">
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endforeach
                                    @endif
                                    @if($type == 'sectionGanged')
                                        @foreach($value as $kind => $contents)
                                            @if($kind == 'first')
                                                @foreach($contents as $relation_ship1 => $value1)
                                                    @foreach($value1 as $relation_ship2 => $value2)
                                                        @foreach($value2 as $key => $content)
                                                            <div class="col-lg-2 form-group searchItem">
                                                                <select name="mixedSearchFields[{{$type}}][first][{{ $relation_ship1 }}][{{ $relation_ship2 }}][{{ $key }}]" class='form-control select_select0 col-lg-2 sectionganged_first'>
                                                                    <option value=''>{{config('setting.transfer_search')[$relation_ship1.'.'.$relation_ship2.'.'.$key]}}</option>
                                                                    @foreach($content as $k => $v)
                                                                        <option value="{{ $k }}" {{request()->has('mixedSearchFields'.'.'.$type.'.first.'.$relation_ship1.'.'.$relation_ship2.'.'.$key) ? ($k==request('mixedSearchFields'.'.'.$type.'.first.'.$relation_ship1.'.'.$relation_ship2.'.'.$key)?'selected':'') : ''}} >{{$v}}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        @endforeach
                                                    @endforeach
                                                @endforeach
                                            @endif
                                            @if($kind == 'second')
                                                @foreach($contents as $name => $content)
                                                    <div class="col-lg-2 form-group searchItem">
                                                        <select name="mixedSearchFields[{{$type}}][second][{{ $name }}]" class='form-control select_select0 col-lg-2 sectionganged_second'>
                                                            <option value=''>{{config('setting.transfer_search')[$name]}}</option>
                                                            @foreach($content as $k => $v)
                                                                <option value="{{ $k }}" {{request()->has('mixedSearchFields'.'.'.$type.'.second.'.$name) ? ($k==request('mixedSearchFields'.'.'.$type.'.second.'.$name)?'selected':'') : ''}} >{{$v}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @endforeach
                                            @endif
                                        @endforeach
                                    @endif

                                    @if($type == 'sectionGangedDouble')
                                        @foreach($value as $kind => $contents)
                                            @if($kind == 'first')
                                                @foreach($contents as $relation_ship1 => $value1)
                                                    @foreach($value1 as $relation_ship2 => $value2)
                                                        @foreach($value2 as $key => $content)
                                                            <div class="col-lg-2 form-group searchItem">
                                                                <select name="mixedSearchFields[{{$type}}][first][{{ $relation_ship1 }}][{{ $relation_ship2 }}][{{ $key }}]" class='form-control select_select0 col-lg-2 sectiongangeddouble_first'>
                                                                    <option value=''>{{config('setting.transfer_search')[$relation_ship1.'.'.$relation_ship2.'.'.$key]}}</option>
                                                                    @foreach($content as $k => $v)
                                                                        <option value="{{ $k }}" {{request()->has('mixedSearchFields'.'.'.$type.'.first.'.$relation_ship1.'.'.$relation_ship2.'.'.$key) ? ($k==request('mixedSearchFields'.'.'.$type.'.first.'.$relation_ship1.'.'.$relation_ship2.'.'.$key)?'selected':'') : ''}} >{{$v}}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        @endforeach
                                                    @endforeach
                                                @endforeach
                                            @endif
                                            @if($kind == 'second')
                                                @foreach($contents as $relation_ship1 => $content)
                                                    @foreach($content as $name => $content1)
                                                        <div class="col-lg-2 form-group searchItem">
                                                            <select name="mixedSearchFields[{{$type}}][second][{{$relation_ship1}}][{{ $name }}]" class='form-control select_select0 col-lg-2 sectiongangeddouble_second'>
                                                                <option value=''>{{config('setting.transfer_search')[$relation_ship1.'.second']}}</option>
                                                                @foreach($content1 as $k => $v)
                                                                    <option value="{{ $k }}" {{request()->has('mixedSearchFields'.'.'.$type.'.second.'.$relation_ship1.'.'.$name) ? ($k==request('mixedSearchFields'.'.'.$type.'.second.'.$relation_ship1.'.'.$name)?'selected':'') : ''}} >{{$v}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    @endforeach
                                                @endforeach
                                            @endif
                                        @endforeach
                                    @endif
                                @endforeach
                                <div class="col-lg-2">
                                    <button class="btn btn-success" type="submit">查询</button>
                                    <a class="btn btn-default" href="{{ request()->url() }}">取消</a>
                                </div>
                            </div>
                        </form>
                        {{--</div>--}}
            </div>
            @endif

            {{--更多查询--}}
                    {{----}}
                @show{{-- 列表工具栏 --}}
                <div class="row">
                    <div class="col-lg-12">
                        <table class="table table-bordered table-striped table-hover sortable">
                            <thead>
                            <tr>
                                @section('tableHeader')@show{{-- 列表字段 --}}
                            </tr>
                            </thead>
                            <tbody>
                            @section('tableBody')@show{{-- 列表数据 --}}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <span>每页&nbsp;</span>
                        <select id="pageSize" data-url="{{ request()->url() }}">
                            @foreach(config('setting.pageSizes') as $page)
                                <option value="{{ $page }}" {{ $page == request()->input('pageSize') ? 'selected' : '' }}>
                                    {{ $page }}
                                </option>
                            @endforeach
                        </select>
                        <span>&nbsp;条，共 {{ $data->total() }} 条</span>
                    </div>
                    <div class="col-lg-6 text-right">
                        {!!
                        $data
                        ->appends([
                        'keywords' => request()->input('keywords'),
                        'pageSize' => request()->input('pageSize'),
                        'sorts' => request()->input('sorts'),
                        'filters' => request()->input('filters'),
                        'filterClear' => request()->input('filterClear'),
                        'mixedSearchFields' => request()->input('mixedSearchFields'),
                        ])
                        ->render()
                        !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div style="display: none">
    <form action="{{ route('Excel.export') }}" method="POST">
        <div class="container" style="padding: 0 15px;">
            <small>按时间导表</small>
            From:<input type="text" name="start_time"  class="datetimepicker">
            to:<input type="text" name="end_time"  class="datetimepicker">
            <input type="submit" value="按时间导出数据" class="ui-button" style="padding:0 .5em">
            (由于邮件类型是4月5号才更新的功能之前回复的数据都是没有的 如:导出20号  请选择 20到21号)
        </div>
    </form>
    <form action="{{ route('messageorder.export') }}" method="POST">
        <div class="container" style="padding: 0 15px;">
            <small>按时间导投诉类型表</small>
            From:<input type="text" name="start_time"  class="datetimepicker">
            to:<input type="text" name="end_time"  class="datetimepicker">
            <input type="submit" value="按时间导出数据" class="ui-button" style="padding:0 .5em">

        </div>
    </form>

    <script type="text/javascript">
        $(function () {
            $('.datetimepicker').datetimepicker({
                minView: "month", //选择日期后，不会再跳转去选择时分秒
                format: "yyyy-mm-dd", //选择日期后，文本框显示的日期格式
                lang:"ch",  //汉化
                autoclose:true //选择日期后自动关闭
            });
        });
    </script>
    </div>
    <div style="margin-left: 20px;">
        <?php
        if(isset($indexFlag))
        {
        ?>
        <table>
            <tr>
                <td valign="top" style="padding-right: 50px;">
                    <form action="{{ route('message.notsRequireReply') }}" method="POST">
                        <div>
                            请输入需要无需回复的id<br/>
                            <textarea name="ids" cols="30" rows="15"></textarea><br/>
                            <input type="submit" value="提交">
                        </div>
                    </form>
                </td>
        <?php
        //判断是否是管理员
        $keader=config('user.keader');
        $user_id=request()->user()->id;
        if(in_array($user_id,$users_leader))
        {
            ?>
                <td valign="top" >
                    <form action="{{ route('message.assigned') }}" method="POST" style="width:230px;" onSubmit="return checkform();">
                        <div>
                            请输入邮件的id(一行一个邮件id)<br/>
                            <textarea name="ids" cols="30" rows="15"></textarea><br/>
                            <select class="form-control select_select0" name="assign_id" >
                                <option id="test">请选择</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="submit" value="提交">
                        </div>
                    </form>
                </td>
            <?php
            }
            ?>
        <?php
        }
        ?>
            </tr>
        </table>
    </div>
    {{-- 模拟DELETE删除表单 --}}
    <form method="POST" action="" id="hiddenDeleteForm">
        {!! csrf_field() !!}
        <input type="hidden" name="_method" value="DELETE"/>
    </form>
@stop
@section('pageJs')
    <script type="text/javascript">
        {{-- 提交删除表单  --}}
        $('.delete_item').click(function () {
            if (confirm("确认删除?")) {
                var url = $(this).data('url');
                $('#hiddenDeleteForm').attr('action', url);
                $('#hiddenDeleteForm').submit();
            }
        });
        {{-- 更改显示条数  --}}
        $('#pageSize').change(function () {
            var size = $(this).val();
            var url = $(this).data('url');
            var action = url + '?pageSize=' + size;
            location.href = action;
        });
        {{-- 排序 --}}
        $('.sort').click(function () {
            var field = $(this).data('field');
            var uri = new URI();
            uri.hasQuery('sorts', function (value) {
                var hasField = 0;
                var sortsNew;
                if (value) {
                    var srotsQuery = value.split(',');
                    $.each(srotsQuery, function (sortsKey, sortsValue) {
                        var sort = sortsValue.split('.');
                        if (sort[0] == field) {
                            hasField = 1;
                            sortsNew = sort[1] == 'asc' ? value.replace(field + '.asc', field + '.desc') : value.replace(field + '.desc', field + '.asc');
                        }
                    });
                    if (hasField == 0) {
                        sortsNew = value + "," + field + '.asc';
                    }
                } else {
                    sortsNew = field + '.asc';
                }
                window.location.href = uri.setQuery('sorts', sortsNew);
            });
        });
        $('.sort').each(function (k, obj) {
            var field = $(obj).data('field');
            var uri = new URI();
            uri.hasQuery('sorts', function (value) {
                if (value) {
                    var srotsQuery = value.split(',');
                    $.each(srotsQuery, function (sortsKey, sortsValue) {
                        var sort = sortsValue.split('.');
                        if (sort[0] == field) {
                            sort[1] == 'asc' ? $(obj).append('<span class="sign arrow up"></span>') : $(obj).append('<span class="sign arrow"></span>');
                        }
                    });
                }
            });
        });

        $('.datetime_select').datetimepicker({theme: 'defalut'});
        $('.select_select0').select2();

    </script>
@stop
@section('childJs')@show