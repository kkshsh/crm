<div class="panel panel-success">
    <div class="panel-heading">订单查询</div>
    <div class="panel-body">
        <form action="{{ route('message.process', ['id'=>$message->id]) }}" method="POST">
            {!! csrf_field() !!}
            <div class="row form-group">
                <div class="col-lg-8">
                    <input type="email" id="select_email" class="form-control" name="email" placeholder="填写Email"/>
                </div>
                <div class="col-lg-4">
                    <button type="submit" class="btn btn-success">
                        <span class="glyphicon glyphicon-search"></span> 查询
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="panel panel-danger">
    <div class="panel-heading">订单关联 -
        <small>如果要处理信息,请先关联订单!</small>
    </div>
    <div class="panel-body">
        <form action="{{ route('message.setRelatedOrders', ['id'=>$message->id]) }}" method="POST">
            {!! csrf_field() !!}
            @if(isset($relatedOrders['smtemail']) and count($relatedOrders['smtemail']) > 0)
            {{--@if(false)--}}
                <div class="panel panel-default">
                    <div class="panel-heading">速卖通历史订单查询</div>
                    <div class="panel-body">
                        @foreach($relatedOrders['smtemail'] as $relatedOrder)
                            <div class="row form-group">
                                {{--<div class="col-lg-1">--}}
                                    {{--<input type="checkbox" name="relatedOrdernums[]" value="{{ $relatedOrder->order->ordernum }}" />--}}
                                {{--</div>--}}
                                <div class="col-lg-4">
                                    <strong>订单号</strong>:
                                    <a href="http://erp.wxzeshang.com:8000/admin/order/order/?q={{ $relatedOrder['ordernum'] }}" target="_blank">
                                        <strong>{{ $relatedOrder['ordernum'] }}</strong>
                                    </a>
                                </div>
                                {{--<div class="col-lg-6">--}}
                                    {{--<strong>邮箱</strong>: {{ $relatedOrder->order->email }}--}}
                                {{--</div>--}}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            {{--@if(isset($ordernum) and count($ordernum) > 0)--}}
            @if(false)
                <div class="panel panel-default">
                    <div class="panel-heading">历史关联</div>
                    <div class="panel-body">
                        @foreach($ordernum as $order_num)
                            <div class="row form-group">
                                <div class="col-lg-1">
                                    <input type="checkbox" name="relatedOrdernums[]" value="{{ $relatedOrder->order->ordernum }}" />
                                </div>
                                <div class="col-lg-4">
                                    <strong>订单号</strong>: {{ $order_num->ordernum }}
                                </div>
                                <div class="col-lg-6">
                                    <strong>邮箱</strong>: {{ $order_num->email }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(isset($relatedOrders['email']) and count($relatedOrders['email']) > 0)
                <div class="panel panel-default">
                    <div class="panel-heading">邮箱关联</div>
                    <div class="panel-body">
                        @foreach($relatedOrders['email'] as $relatedOrder)
                            <div class="row form-group">
                                <div class="col-lg-1">
                                    <input type="checkbox" name="relatedOrdernums[]" value="{{ $relatedOrder['id'] }}" />
                                </div>
                                <div class="col-lg-4">
                                    <strong>订单号</strong>: {{ $relatedOrder['ordernum'] }}
                                </div>
                                <div class="col-lg-6">
                                    <strong>邮箱</strong>: {{ $relatedOrder['email'] }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            <div class="row form-group">
                <div class="col-lg-12">
                    <input type="text" class="form-control" name="numbers" placeholder="填写订单号,多个用英文逗号分隔"/>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-lg-6">
                    <button type="submit" class="btn btn-danger">
                        <span class="glyphicon glyphicon-link"></span> 关联订单
                    </button>
                </div>
                <div class="col-lg-6 text-right">
                    <button class="btn btn-warning" type="button" onclick="if(confirm('确认无需关联订单?')){location.href='{{ route('message.notRelatedOrder', ['id'=>$message->id]) }}'}">
                        <span class="glyphicon glyphicon-minus-sign"></span> 无需关联订单
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>