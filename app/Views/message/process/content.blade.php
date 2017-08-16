<div class="panel panel-info">
    <div class="panel-heading">
        {!! $message->label_text !!}
        <strong>{!! $message->subject !!}</strong><br/>
        <small>
            {{ $message->date }} by <i>{{ $message->from_name }}</i> from {{ '<'.$message->from.'>' }}
        </small>
        <!--
        <button class="btn btn-primary" data-toggle="modal"
                data-target="#myModal123">
            转发(暂时不要点)
        </button>
        -->
        原邮箱:{{$message->to}}
        <a href="javascript:" class="close" data-toggle="modal" data-target="#myModal">
            <small class="glyphicon glyphicon-list"></small>
        </a>
        &nbsp;&nbsp;
            <span class="" style="color:red;float:right;margin-right: 10px;font-size: 15px;">{{$count}}</span>
        &nbsp;&nbsp;

    </div>
	
    <div class="panel-body">
        <div class="row">
            <div class="col-lg-12">
                {!! $message->MessageInfo !!}

                {{--                <div class="embed-responsive embed-responsive-16by9">
                                    <iframe class="embed-responsive-item" src="{{ route('message.content', ['id'=>$message->id]) }}"></iframe>
                                </div>--}}
            </div>
        </div>
        @if(count($message->message_attanchments) > 0)
            <hr>
            @foreach($message->message_attanchments as $attanchment)
                <div class="row">
                    <div class="col-lg-12">
                        <strong>附件</strong>:
                        <a href="{{ $attanchment['filepath'] }}" target="_blank">{{ $attanchment['filename'] }}</a>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>
<!-- 模态框（Modal） -->
<div class="modal fade" id="myModal123" tabindex="-1" role="dialog"
     aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close"
                        data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    转发邮件
                </h4>
            </div>
            <form class="bs-example bs-example-form" role="form" action="{{ route('message.foremail', ['id'=>$message->id]) }}" method="POST">
                <label for="name">请选择发件人</label>
                <select class="form-control" name="to">
                    @foreach($accounts as $account_id)
                        <option value="{{ $account_id['account'] }}">{{ $account_id['account'] }}</option>
                    @endforeach
                </select>
                <br>
                <div class="input-group">
                    <span class="input-group-addon">收件人邮箱</span>
                    <input type="email" name="email" class="form-control" placeholder="请输入邮箱">
                </div>
                <br>

                <div class="input-group">
                    <span class="input-group-addon">note转发描述</span>
                    <input type="text" name="note" class="form-control" placeholder="note转发描述">
                </div>
                <br>
                <input type="hidden" name="content" value="<iframe class='embed-responsive-item' src='{{ route('message.content', ['id'=>$message->id]) }}'></iframe>"/>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default"
                            data-dismiss="modal">关闭
                    </button>
                    <button type="submit" class="btn btn-primary">
                        提交更改
                    </button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal -->
</div>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" style="width:1000px;" role="document" style="width:1000px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">邮件历史</h4>
            </div>
            <div class="modal-body">
			
                @foreach($message->histories->take(5) as $history)
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Message <a href="{{ url('message',$history->id)}}">#{{ $history->id }}</a></strong>
							
							<br/>
                        </div>
                        <div class="panel-body">
                            <div class="panel panel-primary">
                                <div class="panel-heading">
                                    <strong>{!! $history->subject !!}</strong><br/>
                                </div>
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-lg-12">
{{--
                                            {!! $history->message_info !!}
--}}
                                            {{-- <div class="embed-responsive embed-responsive-16by9">
                                                 <iframe class="embed-responsive-item" src="{{ route('message.content', ['id'=>$history->id]) }}"></iframe>
                                             </div>--}}
                                        </div>
                                    </div>
                                </div>
                                <div class="panel-footer">
                                    <small>
                                        {{ $history->date }} by <strong>{{ $history->from_name }}</strong>
                                        from {{ '<'.$history->from.'>' }}
                                    </small>
                                </div>
                            </div>
                            @if($history->required)
                                @foreach($history->replies as $reply)
                                    <div class="panel panel-info">
                                        <div class="panel-heading">
                                            <strong>{{ $reply->title }}</strong><br/>
                                        </div>
                                        <div class="panel-body">
                                            <div class="row">
                                                <div class="col-lg-12 pre-scrollable">
                                                    {!! $reply->content !!}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="panel-footer">
                                            <small>
                                                {{ $reply->created_at }} by
                                                <strong>
                                                    <?php echo !empty($history->assigner) ? $history->assigner->name : '未知' ?>
                                                </strong>
                                                from {{ '<'.$history->to.'>' }}
                                            </small>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="alert alert-warning">
                                            此条信息被 <strong>
                                                <u>
                                                    <?php echo !empty($history->assigner) ? $history->assigner->name : '未知' ?>
                                                </u>
                                            </strong> 标注为无需回复
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>