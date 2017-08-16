@extends('layouts.default')
@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <span class="label label-success">{{$case->open_reason}}</span><br/>
                    <strong>CaseId:&nbsp;{{$case->case_id}}</strong>
                    <small>
                        <i><strong>{{ $case->buyer_id }}</strong></i>&nbsp;&nbsp;&nbsp; Date:&nbsp;{{ $case->creation_date }}
                    </small><br/>
                    <strong>Title:&nbsp;{{$case->item_title}}</strong><br/>

                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-12">
                            {!!$case->CaseContent!!}
                        </div>
                    </div>

                </div>
            </div>
            <div class="panel panel-danger">
                <div class="panel-heading">
                    处理
                </div>

                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <ul class="nav nav-pills" role="tablist">
                                <li role="presentation" class="active"><a href="#tracking" aria-controls="tracking" role="tab" data-toggle="tab">Add tracking details</a></li>
                                <li role="presentation"><a  href="#refund" aria-controls="refund" role="tab" data-toggle="tab" >Refund the buyer</a></li>
                                <li role="presentation"><a  href="#message" aria-controls="message" role="tab" data-toggle="tab">Send a message to the buyer</a></li>
                                @if($case->type == 'EBP_SNAD') <!--return case 可以部分退款-->
                                <li role="presentation"><a  href="#part-refund" aria-controls="message" role="tab" data-toggle="tab">Part refund the buyer</a></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                    <!--case 操作表单begin-->
                    <div class="row">
                        <div class="col-lg-12 tab-content">


                            <div tabindex="1" id="tracking" role="tabpanel" class="tab-pane active">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                    </div>
                                    <div class="panel-body">
                                        <form class="form-horizontal track-form" action="{{route('AddTrackingDetails')}}">
                                            {!! csrf_field() !!}
                                            <input type="hidden" name="is_tracked" value="10"><!--是否追踪号-->
                                            <input type="hidden" name="id" value="{{$case->id}}">
                                            <div>
                                                <ul class="nav nav-tabs" role="tablist">
                                                    <li role="presentation" class="active istracked" track="10"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">有追踪号</a></li>
                                                    <li role="presentation" class="istracked" track="-10"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">没有追踪号</a></li>
                                                </ul>
                                                <div class="tab-content">
                                                    <div role="tabpanel" class="tab-pane active" id="home">
                                                        <div class="form-group">
                                                            <label for="inputEmail3" class="col-sm-2 control-label">trackingNumber<small class="text-danger glyphicon glyphicon-asterisk"></small></label>
                                                            <div class="col-sm-10">
                                                                <input type="email" class="form-control" id="inputEmail3" name="trackingNumber" placeholder="trackingNumber">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="inputEmail3" class="col-sm-2 control-label">carrier<small class="text-danger glyphicon glyphicon-asterisk"></small></label>
                                                            <div class="col-sm-10">
                                                                <input type="email" class="form-control" id="inputEmail3" name="carrier"  placeholder="carrier">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div role="tabpanel" class="tab-pane" id="profile">
                                                        <div class="form-group">
                                                            <label for="inputEmail3" class="col-sm-2 control-label">carrierUsed<small class="text-danger glyphicon glyphicon-asterisk"></small></label>
                                                            <div class="col-sm-10">
                                                                <input type="email" class="form-control" id="inputEmail3" name="carrierUsed" placeholder="carrierUsed">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="inputEmail3" class="col-sm-2 control-label">shippedDate<small class="text-danger glyphicon glyphicon-asterisk"></small></label>
                                                            <div class="col-sm-10">
                                                                <input type="email" class="form-control" id="inputEmail3" name="shippedDate" placeholder="shippedDate">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <label>comments: </label>
                                            <textarea class="form-control" rows="3" name="comments"></textarea>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <button type="button" class="btn btn-success do-tracking" style="float: right">提交</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>


                            <div role="tabpanel" class="tab-pane panel-default" id="refund">
                                <div class="panel-heading">
                                </div>
                                <div class="panel-body">
                                    <form class="form-horizontal" action="{{route('case.RefundBuyer')}}" METHOD="POST" id="refund-form">
                                        <input type="hidden" name="id" value="{{$case->id}}">
                                        {!! csrf_field() !!}
                                        <label>退款原因: </label>
                                        <select class="form-control" name="refund-reason">
                                            @foreach(config('order.reason') as $key => $reason)
                                                <option value="{{$key}}">{{$reason}}</option>
                                            @endforeach
                                        </select>
                                        <label>备注: </label>
                                        <textarea class="form-control" rows="3" name="comment"></textarea>
                                        <div class="row">
                                            <div class="col-lg-12">

                                                <button type="button" class="btn btn-success submit-refund" style="float: right">提交</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div role="tabpanel" class="tab-pane panel-default" id="message">
                                <div class="panel-heading">
                                </div>
                                <div class="panel-body">
                                    <form class="form-horizontal" action="{{route('MessageToBuyer')}}">
                                        {!! csrf_field() !!}
                                        <label>comments: </label>
                                        <input type="hidden" name="id" value="{{$case->id}}">
                                        <textarea class="form-control" rows="3" name="messgae_content"></textarea>
                                        <div class="row">
                                            <div class="col-lg-12">

                                                <button type="submit" class="btn btn-success" style="float: right">提交</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>


                            <div role="tabpanel" class="tab-pane panel-default" id="part-refund">
                                <div class="panel-heading">
                                </div>
                                <div class="panel-body">
                                    <form class="form-horizontal" action="{{route('case.PartRefundBuyer')}}" METHOD="POST" id="refund-form">
                                        <input type="hidden" name="id" value="{{$case->id}}">
                                        {!! csrf_field() !!}
                                        <small class="text-danger glyphicon glyphicon-asterisk"></small><label>退款金额: </label>
                                        <input type="text" name="amount" class="form-control" />
                                        <label>退款原因: </label>
                                        <select class="form-control" name="refund-reason">
                                            @foreach(config('order.reason') as $key => $reason)
                                                <option value="{{$key}}">{{$reason}}</option>
                                            @endforeach
                                        </select>
                                        <label>备注: </label>
                                        <textarea class="form-control" rows="3" name="comment"></textarea>
                                        <div class="row">
                                            <div class="col-lg-12">

                                                <button type="button" class="btn btn-success submit-refund" style="float: right">提交</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>



                        </div>
                    </div>
                    <!--case 操作表单  end-->

                </div>
            </div>

        </div>

        <div class="col-lg-4">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    Cases详情
                </div>

                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-12">

                            <ul class="list-group">
                                <li class="list-group-item ">ItemID: {{$case->case_id}}</li>
                                <li class="list-group-item ">总金额：{{$case->case_amount}}</li>
                                <li class="list-group-item ">数量：{{$case->case_quantity}}</li>
                                <li class="list-group-item ">最后回复时间：{{$case->last_modify_date}}</li>

                            </ul>
                        </div>
                    </div>

                </div>
            </div>

        @if($case_order_info)
                @include('message.ebay_cases.order')
            @else
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        订单信息
                    </div>

                    <div class="panel-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <p>ERP暂时没有匹配到订单信息┭┮﹏┭┮</p>
                            </div>

                        </div>
                    </div>
                </div>
        @endif
        </div>
@stop
@section('pageJs')
    <script>
        $(document).ready(function(){
            $('.istracked').click(function(){
                var is_tracked = $(this).attr('track');
                $("input[name='is_tracked']").val(is_tracked);
            });
            $('.do-tracking').click(function(){

                if($("input[name='is_tracked']").val() == 10){
                    if($("input[name='trackingNumber']").val() == ''){
                        alert('trackingNumber 不能为空');
                        return;
                    }
                    if($("input[name='carrier']").val() == ''){
                        alert('carrier 不能为空');
                        return;
                    }
                }else if($("input[name='is_tracked']").val() == -10){
                    if($("input[name='carrierUsed']").val() == ''){
                        alert('carrierUsed 不能为空');
                        return;
                    }
                    if($("input[name='shippedDate']").val() == ''){
                        alert('shippedDate 不能为空');
                        return;
                    }
                }

                $('.track-form').submit();
            });

            $('.submit-refund').click(function(){
                if(confirm('确定退款？')){
                    $('#refund-form').submit();
                }
            });


/*            $('.process-tab').click(function () {
                var index = $(this).attr('tabindex');
                $('.process-tab').removeClass('active');
                $(this).addClass('active');
                $('.tab-content').each(function(){
                    $(this).addClass('tabhide');
                    if($(this).attr('tabindex') == index){
                        $(this).removeClass('tabhide');
                        return;
                    }
                });
            });*/


        });
    </script>

















































@stop