@extends('layouts.default')
@section('content')

    <div class="row">
        <div class="col-lg-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
				
                    {!! $model->label_text !!}
                    <strong>{{ $model->subject }}</strong><br/>
					
                    <small>
                        {{ $model->date }} by <strong><u>{{ $model->from_name }}</u></strong>
                        from {{ '<'.$model->from.'>' }}
                    </small>

                    原邮箱:{{ $model->to }}
                    <a href="javascript:" class="close" data-toggle="modal" data-target="#myModal">
                        <small class="glyphicon glyphicon-list"></small>
                    </a>
					<span class="" style="color:red;float:right;margin-right: 10px;font-size: 15px;">{{$count}}</span>
                </div>
				
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="embed-responsive embed-responsive-16by9">
                                {!! $model->MessageInfo !!}
                                {{--<iframe class="embed-responsive-item" src="{{ route('message.content', ['id'=>$model->id]) }}"></iframe>--}}
                            </div>
                        </div>
                    </div>
                    @if(count($model->message_attanchments) > 0)
                        <hr>
                        @foreach($model->message_attanchments as $attanchment)
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
            @if($model->required)
                @foreach($model->replies as $reply)
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <strong>{{ $reply->title }}</strong><br/>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-12 pre-scrollable">
                                    {!! nl2br($reply->content) !!}
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <small>
                                {{ $reply->created_at }} by <strong><u>{{ $model->assigner->name }}</u></strong>
                                from {{ '<'.$model->to.'>' }}
                            </small>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="row">
                    <div class="col-lg-12">
                        <div class="alert alert-warning">
                            此条信息被 <strong><u>{{ $model->assigner->name }}</u></strong> 标注为无需回复
                        </div>
                    </div>
                </div>
            @endif
            <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                <div class="modal-dialog" role="document" style="width:1000px;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">邮件历史</h4>
                        </div>
                        <div class="modal-body">
                            @foreach($model->histories->take(5) as $history)
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <strong>Message <a href="{{ url('message',$history->id)}}">#{{ $history->id }}</a></strong>
										<strong style="float:right">
											@if($history->status=='UNREAD')
												未读
											@endif
											@if($history->status=='PROCESS')
												待处理
											@endif
											@if($history->status=='COMPLETE')
												已处理
											@endif
										</strong>
										<br/>
										
                                    </div>
									<div>
									
									</div>
                                    <div class="panel-body">
                                        <div class="panel panel-primary">
                                            <div class="panel-heading">
                                                <strong>{{ $history->subject }}</strong><br/>
                                            </div>
                                            <div class="panel-body">
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="embed-responsive embed-responsive-16by9">
                                                            <iframe class="embed-responsive-item" src="{{ route('message.content', ['id'=>$history->id]) }}"></iframe>
                                                        </div>
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
                                                            <u>
                                                                <?php echo !empty($history->assigner) ? $history->assigner->name : '未知' ?>
                                                            </u>
                                                        </strong>
                                                        from {{ '<'.$history->to.'>' }}
                                                    </small>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{--<div class="col-lg-6">--}}
            {{--@if($model->related)--}}
                {{--@if($model->relatedOrders()->count() > 1)--}}
                    {{--<ul class="nav nav-tabs" role="tablist">--}}
                        {{--@foreach($model->relatedOrders as $key => $relatedOrder)--}}
                            {{--<li role="presentation" class="{{ $key == 0 ? 'active' : '' }}">--}}
                                {{--<a href="#{{ $relatedOrder->order->ordernum }}"--}}
                                   {{--aria-controls="{{ $relatedOrder->order->ordernum }}"--}}
                                   {{--role="tab"--}}
                                   {{--data-toggle="tab">--}}
                                    {{--{{ $relatedOrder->order->ordernum }}--}}
                                {{--</a>--}}
                            {{--</li>--}}
                        {{--@endforeach--}}
                    {{--</ul>--}}
                {{--@endif--}}
                {{--<div class="tab-content">--}}
                    {{--@foreach($model->relatedOrders as $key => $relatedOrder)--}}
                        {{--<div class="tab-pane {{ $key == 0 ? 'active' : '' }}" role="tabpanel" id="{{ $relatedOrder->order->ordernum }}">--}}
                            {{--<div class="panel panel-default">--}}
                                {{--<div class="panel-heading">--}}
                                    {{--订单:--}}
                                    {{--<a href="http://ws.jinjidexiaoxuesheng.com/admin/workstation/order/{{ $relatedOrder->order->id }}" target="_blank">--}}
                                    {{--<a href="http://erp.wxzeshang.com:8000/admin/order/order/?q={{ $relatedOrder->order->ordernum }}" target="_blank">--}}
                                        {{--<strong>{{ $relatedOrder->order->ordernum }}</strong>--}}
                                    {{--</a>--}}
                                    {{--(旧WS：<a href="http://ws.jinjidexiaoxuesheng.com:8888/admin/workstation/order/{{ $relatedOrder->order->id }}" target="_blank">--}}
                                        {{--<strong>{{ $relatedOrder->order->ordernum }}</strong>--}}
                                    {{--</a>)--}}
                                    {{--<small>{{ '<'.$relatedOrder->order->email.'>' }}</small>--}}
                                    {{-----}}
                                    {{--<strong>{{ $relatedOrder->order->status_text }}</strong>--}}
                                    {{-----}}
                                    {{--<strong>{{ $relatedOrder->order->active_text }}</strong>--}}

                                    {{--<div class="close">--}}
                                        {{--<a href="javascript:void(0);" onclick="if(confirm('确认取消订单: {{ $relatedOrder->order->ordernum }} ?')){location.href='{{ route('message.cancelRelatedOrder', ['id'=>$model->id,'relatedOrderId'=>$relatedOrder->id]) }}'}">--}}
                                            {{--<small class="glyphicon glyphicon glyphicon-off"></small>--}}
                                        {{--</a>--}}
                                    {{--</div>--}}
                                {{--</div>--}}
                                {{--<div class="panel-body">--}}
                                    {{--<div class="row form-group">--}}
                                        {{--<div class="col-lg-3">--}}
                                            {{--<strong>订单总额</strong>: {{ $relatedOrder->order->amount }} {{ $relatedOrder->order->currency }}--}}
                                        {{--</div>--}}
                                        {{--<div class="col-lg-3">--}}
                                            {{--<strong>产品金额</strong>: {{ $relatedOrder->order->amount_product }} {{ $relatedOrder->order->currency }}--}}
                                        {{--</div>--}}
                                        {{--<div class="col-lg-3">--}}
                                            {{--<strong>运费金额</strong>: {{ $relatedOrder->order->amount_shipping }} {{ $relatedOrder->order->currency }}--}}
                                        {{--</div>--}}
                                        {{--<div class="col-lg-3">--}}
                                            {{--<strong>促销金额</strong>: {{ $relatedOrder->order->amount_coupon }} {{ $relatedOrder->order->currency }}--}}
                                        {{--</div>--}}
                                    {{--</div>--}}
                                    {{--<div class="row form-group">--}}
                                        {{--<div class="col-lg-12">--}}
                                            {{--<strong>运送地址</strong>:--}}
                                            {{--{{ $relatedOrder->order->shipping_firstname }}--}}
                                            {{--{{ $relatedOrder->order->shipping_lastname }},--}}
                                            {{--{{ $relatedOrder->order->shipping_address }},--}}
                                            {{--{{ $relatedOrder->order->shipping_address1 ? $relatedOrder->order->shipping_address1.',' : '' }}--}}
                                            {{--{{ $relatedOrder->order->shipping_city }},--}}
                                            {{--{{ $relatedOrder->order->shipping_state }},--}}
                                            {{--{{ $relatedOrder->order->shipping_country }},--}}
                                            {{--{{ $relatedOrder->order->shipping_zipcode }},--}}
                                            {{--{{ $relatedOrder->order->shipping_phone }}--}}
                                        {{--</div>--}}
                                    {{--</div>--}}
                                    {{--<div class="row">--}}
                                        {{--<div class="col-lg-12">--}}
                                            {{--<table class="table table-bordered">--}}
                                                {{--<thead>--}}
                                                {{--<tr>--}}
                                                    {{--<th>SKU</th>--}}
                                                    {{--<th>Qty</th>--}}
                                                    {{--<th>Price</th>--}}
                                                    {{--<th>Status</th>--}}
                                                {{--</tr>--}}
                                                {{--</thead>--}}
                                                {{--@foreach($relatedOrder->order->items as $item)--}}
                                                    {{--<tr>--}}
                                                        {{--<td>{{ $item->sku }}</td>--}}
                                                        {{--<td>{{ $item->qty }}</td>--}}
                                                        {{--<td>{{ $item->price }}</td>--}}
                                                        {{--<td>{{ $item->status_text }}</td>--}}
                                                    {{--</tr>--}}
                                                {{--@endforeach--}}
                                            {{--</table>--}}
                                        {{--</div>--}}
                                    {{--</div>--}}
                                {{--</div>--}}
                                {{--<div class="panel-footer">--}}
                                    {{--<div class="row">--}}
                                        {{--<div class="col-lg-6">--}}
                                            {{--<strong>创建时间</strong>: {{ $relatedOrder->order->create_time }}--}}
                                        {{--</div>--}}
                                        {{--<div class="col-lg-6">--}}
                                            {{--<strong>支付时间</strong>: {{ $relatedOrder->order->payment_date }}--}}
                                        {{--</div>--}}
                                    {{--</div>--}}
                                {{--</div>--}}
                            {{--</div>--}}
                            {{--@foreach($relatedOrder->order->packages as $package)--}}
                                {{--<div class="panel panel-success">--}}
                                    {{--<div class="panel-heading">--}}
                                        {{--Package:--}}
                                        {{--<a href="http://ws.jinjidexiaoxuesheng.com/admin/workstation/package/{{ $package->id }}" target="_blank">--}}
                                        {{--<a href="http://erp.wxzeshang.com:8000/admin/shipping/package/?q={{ $package->id }}" target="_blank">--}}
                                            {{--<strong>#{{ $package->id }}</strong>--}}
                                        {{--</a>--}}
                                        {{--(旧WS：<a href="http://ws.jinjidexiaoxuesheng.com:8888/admin/workstation/package/{{ $package->id }}" target="_blank">--}}
                                            {{--<strong>#{{ $package->id }}</strong>--}}
                                        {{--</a>)--}}
                                        {{-----}}
                                        {{--<strong>{{ $package->status_text }}</strong>--}}
                                    {{--</div>--}}
                                    {{--@if($package->shipping)--}}
                                        {{--<div class="panel-body">--}}
                                            {{--<div class="row form-group">--}}
                                                {{--<div class="col-lg-4">--}}
                                                    {{--<strong>物流</strong>:--}}
                                                    {{--<a href="{{ $package->shipping->link }}" target="_blank">--}}
                                                        {{--{{ $package->shipping->name }}--}}
                                                    {{--</a>--}}
                                                {{--</div>--}}
                                                {{--<div class="col-lg-4">--}}
                                                    {{--<strong>物流网址</strong>:--}}
                                                        {{--{{ $package->shipping->link }}--}}
                                                {{--</div>--}}
                                                {{--<div class="col-lg-4">--}}
                                                    {{--<strong>追踪号</strong>: {{ $package->tracking_no }}--}}
                                                {{--</div>--}}
                                                {{--<div class="col-lg-4">--}}
                                                    {{--<strong>妥投</strong>:--}}
                                                    {{--@if($package->delivery_time)--}}
                                                        {{--{{ $package->delivery_time }}--}}
                                                        {{--({{ $package->delivery_age }}天)--}}
                                                    {{--@else--}}
                                                        {{------}}
                                                    {{--@endif--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="row form-group">--}}
                                                {{--<div class="col-lg-4">--}}
                                                    {{--<strong>创建</strong>: {{ $package->created }}--}}
                                                {{--</div>--}}
                                                {{--<div class="col-lg-4">--}}
                                                    {{--<strong>打印</strong>: {{ $package->print_time }}--}}
                                                {{--</div>--}}
                                                {{--<div class="col-lg-4">--}}
                                                    {{--<strong>发货</strong>: {{ $package->ship_time }}--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="row">--}}
                                                {{--<div class="col-lg-12">--}}
                                                    {{--<table class="table table-bordered">--}}
                                                        {{--<thead>--}}
                                                        {{--<tr>--}}
                                                            {{--<th>Item #</th>--}}
                                                            {{--<th>Qty</th>--}}
                                                        {{--</tr>--}}
                                                        {{--</thead>--}}
                                                        {{--@foreach($package->items as $item)--}}
                                                            {{--<tr>--}}
                                                                {{--<td>{{ $item->item->sku }}</td>--}}
                                                                {{--<td>{{ $item->qty }}</td>--}}
                                                            {{--</tr>--}}
                                                        {{--@endforeach--}}
                                                    {{--</table>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="row">--}}
                                                {{--<div class="col-lg-12">--}}
                                                    {{--<div class="panel panel-warning">--}}
                                                        {{--<div class="panel-heading">追踪信息</div>--}}
                                                        {{--<div class="panel-body">--}}
                                                            {{--<div class="row">--}}
                                                                {{--<div class="col-lg-12">--}}
                                                                    {{--{{ $package->latest_trackinginfo ? $package->latest_trackinginfo : '暂无追踪信息' }}--}}
                                                                {{--</div>--}}
                                                            {{--</div>--}}
                                                        {{--</div>--}}
                                                        {{--<div class="panel-footer">--}}
                                                            {{--<div class="row">--}}
                                                                {{--<div class="col-lg-12">--}}
                                                                    {{--<strong>更新时间</strong>: {{ $package->delivery_search_time }}--}}
                                                                {{--</div>--}}
                                                            {{--</div>--}}
                                                        {{--</div>--}}
                                                    {{--</div>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                        {{--</div>--}}
                                    {{--@endif--}}
                                {{--</div>--}}
                            {{--@endforeach--}}
                        {{--</div>--}}
                    {{--@endforeach--}}
                {{--</div>--}}
            {{--@endif--}}
        {{--</div>--}}



        <div class="col-lg-6">
            @if($model->related)
                @if(count($erp_apiOrderinfo) > 1)
                    <ul class="nav nav-tabs" role="tablist">
                        @foreach($erp_apiOrderinfo as $key => $relatedOrder)
                            <li role="presentation" class="{{ $key == 0 ? 'active' : '' }}">
                                <a href="#{{ $relatedOrder['ordernum'] }}"
                                   aria-controls="{{ $relatedOrder['ordernum'] }}"
                                   role="tab"
                                   data-toggle="tab">
                                    {{ $relatedOrder['ordernum'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <div class="tab-content">
                    @foreach($erp_apiOrderinfo as $key => $relatedOrder)
                        <div class="tab-pane {{ $key == 0 ? 'active' : '' }}" role="tabpanel" id="{{ $relatedOrder['ordernum'] }}">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    订单:
                                    {{--<a href="http://ws.jinjidexiaoxuesheng.com/admin/workstation/order/{{ $relatedOrder->order->id }}" target="_blank">--}}
                                    <a href="http://erp.wxzeshang.com:8000/admin/order/order/?q={{ $relatedOrder['ordernum'] }}" target="_blank">
                                        <strong>{{ $relatedOrder['ordernum'] }}</strong>
                                    </a>
                                    (旧WS：<a href="http://ws.jinjidexiaoxuesheng.com:8888/admin/workstation/order/{{ $relatedOrder['id'] }}" target="_blank">
                                        <strong>{{ $relatedOrder['ordernum'] }}</strong>
                                    </a>)
                                    <small>{{ '<'.$relatedOrder['email'].'>' }}</small>
                                    -
                                    <strong>{{ $relatedOrder['status'] }}</strong>
                                    -
                                    <strong>{{ $relatedOrder['active'] }}</strong>
                                    -
                                    <strong><?php if ($relatedOrder['is_fba']) echo 'FBA单'; else echo '非FBA单';?> </strong>

                                    <div class="close">
                                        <a href="javascript:void(0);" onclick="if(confirm('确认取消订单: {{ $relatedOrder['ordernum'] }} ?')){location.href='{{ route('message.cancelRelatedOrder', ['id'=>$model->id,'relatedOrderId'=>$relatedOrder['msg_orders_id'] ]) }}'}">
                                            <small class="glyphicon glyphicon glyphicon-off"></small>
                                        </a>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <div class="row form-group">
                                        <div class="col-lg-3">
                                            <strong>订单总额</strong>: {{ $relatedOrder['amount'] }} {{ $relatedOrder['currency'] }}
                                        </div>
                                        <div class="col-lg-3">
                                            <strong>产品金额</strong>: {{ $relatedOrder['amount_product'] }} {{ $relatedOrder['currency'] }}
                                        </div>
                                        <div class="col-lg-3">
                                            <strong>运费金额</strong>: {{ $relatedOrder['amount_shipping'] }} {{ $relatedOrder['currency'] }}
                                        </div>
                                        {{--<div class="col-lg-3">--}}
                                            {{--<strong>促销金额</strong>: {{ $relatedOrder->order->amount_coupon }} {{ $relatedOrder->order->currency }}--}}
                                        {{--</div>--}}
                                    </div>
                                    <div class="row form-group">
                                        <div class="col-lg-12">
                                            <strong>运送地址</strong>:
                                            {{ $relatedOrder['shipping_firstname'] }}
                                            {{ $relatedOrder['shipping_lastname'] }},
                                            {{ $relatedOrder['shipping_address'] }},
                                            {{ $relatedOrder['shipping_address1'] }}
                                            {{ $relatedOrder['shipping_city'] }},
                                            {{ $relatedOrder['shipping_state'] }},
                                            {{ $relatedOrder['shipping_country'] }},
                                            {{ $relatedOrder['shipping_zipcode'] }},
                                            {{ $relatedOrder['shipping_phone'] }}
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <table class="table table-bordered">
                                                <thead>
                                                <tr>
                                                    <th>SKU</th>
                                                    <th>Qty</th>
                                                    <th>Price</th>
                                                    <th>Status</th>
                                                </tr>
                                                </thead>
                                                @foreach($relatedOrder['orderitem_info'] as $item)
                                                    <tr>
                                                        <td>{{ $item['sku'] }}</td>
                                                        <td>{{ $item['qty'] }}</td>
                                                        <td>{{ $item['price'] }}</td>
                                                        <td>{{ $item['deleted'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="panel-footer">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <strong>创建时间</strong>: {{ $relatedOrder['create_time'] }}
                                        </div>
                                        <div class="col-lg-6">
                                            <strong>支付时间</strong>: {{ $relatedOrder['create_time'] }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @foreach($relatedOrder['package_info'] as $package)
                                <div class="panel panel-success">
                                    <div class="panel-heading">
                                        Package:
                                        {{--<a href="http://ws.jinjidexiaoxuesheng.com/admin/workstation/package/{{ $package->id }}" target="_blank">--}}
                                        <a href="http://erp.wxzeshang.com:8000/admin/shipping/package/?q={{ $relatedOrder['ordernum'] }}" target="_blank">
                                            <strong>#{{ $package['id'] }}</strong>
                                        </a>
                                        (旧WS：<a href="http://ws.jinjidexiaoxuesheng.com:8888/admin/workstation/package/{{ $relatedOrder['ordernum'] }}" target="_blank">
                                            <strong>#{{ $package['id'] }}</strong>
                                        </a>)
                                        -
                                        <strong>{{ $package['status'] }}</strong>
                                    </div>
                                    @if($package['shipping_name'])
                                        <div class="panel-body">
                                            <div class="row form-group">
                                                <div class="col-lg-4">
                                                    <strong>物流</strong>:
                                                    <a href="{{ $package['tracking_link'] }}" target="_blank">
                                                        {{ $package['shipping_name'] }}
                                                    </a>
                                                </div>
                                                <div class="col-lg-4">
                                                    <strong>物流网址</strong>:
                                                        {{ $package['tracking_link'] }}
                                                </div>
                                                <div class="col-lg-4">
                                                    <strong>追踪号</strong>: {{ $package['tracking_no'] }}
                                                </div>
                                                <div class="col-lg-4">
                                                    <strong>妥投</strong>:
                                                    @if($package['delivery_time'])
                                                        {{ $package['delivery_time'] }}
                                                        ({{ $package['delivery_age'] }}天)
                                                    @else
                                                        --
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="row form-group">
                                                <div class="col-lg-4">
                                                    <strong>创建</strong>: {{ $package['created'] }}
                                                </div>
                                                <div class="col-lg-4">
                                                    <strong>打印</strong>: {{ $package['print_time'] }}
                                                </div>
                                                <div class="col-lg-4">
                                                    <strong>发货</strong>: {{ $package['ship_time'] }}
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                        <tr>
                                                            <th>Item #</th>
                                                            <th>Qty</th>
                                                        </tr>
                                                        </thead>
                                                        @foreach($package['packageitem_info'] as $item)
                                                            <tr>
                                                                <td>{{ $item['sku'] }}</td>
                                                                <td>{{ $item['qty'] }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="row">
                                            <div class="col-lg-12">
                                                <div class="panel panel-warning">
                                                    <div class="panel-heading">追踪信息</div>
                                                    <div class="panel-body">
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                {{ $package['latest_trackinginfo'] ? $package['latest_trackinginfo'] : '暂无追踪信息' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="panel-footer">
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <strong>更新时间</strong>: {{ $package['delivery_search_time'] }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading">日志信息</div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <strong>创建时间</strong>: {{ $model->created_at }}
                        </div>
                        <div class="col-lg-6">
                            <strong>更新时间</strong>: {{ $model->updated_at }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop