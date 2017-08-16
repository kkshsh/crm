<div class="panel panel-primary">
    <div class="panel-heading">
        订单信息
    </div>

    <div class="panel-body">
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        订单:
                        <a href="{{ route('order.show', ['id'=>$case_order_info->order_id]) }}" target="_blank">
                            <strong>{{$case_order_info->order->id}}</strong>
                        </a>
                        <small>-<strong>Email</strong>:{{$case_order_info->order->email}}</small>
                        -
                        <strong>{{$case_order_info->order->status_text}}</strong>
                        -
                        <strong>{{$case_order_info->order->active_text}}</strong>

{{--                        <div class="close">
                            <a href="javascript:void(0);" onclick="if(confirm('确认取消此关联订单: 1468755215.235 ?')){location.href='http://jiangdi.zserp.com/message/36448/cancelRelatedOrder/17058'}">
                                <small class="glyphicon glyphicon glyphicon-off"></small>
                            </a>
                        </div>--}}
                    </div>
                    <div class="panel-body">
                        <div class="row form-group">
                            <div class="col-lg-3">
                                <strong>总额</strong>: {{ $case_order_info->order->amount }} {{ $case_order_info->order->currency }}
                            </div>
                            <div class="col-lg-3">
                                <strong>产品</strong>: {{ $case_order_info->order->amount_product }} {{ $case_order_info->order->currency }}
                            </div>
                            <div class="col-lg-3">
                                <strong>运费</strong>: {{ $case_order_info->order->amount_shipping }} {{ $case_order_info->order->currency }}
                            </div>
                            <div class="col-lg-3">
                                <strong>促销</strong>: {{ $case_order_info->order->amount_coupon }} {{ $case_order_info->order->currency }}
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-lg-12">
                                <strong>运送地址</strong>:
                                {{ $case_order_info->order->shipping_firstname }}
                                {{ $case_order_info->order->shipping_lastname }},
                                {{ $case_order_info->order->shipping_address }},
                                {{ $case_order_info->order->shipping_address1 ? $case_order_info->order->shipping_address1.',' : '' }}
                                {{ $case_order_info->order->shipping_city }},
                                {{ $case_order_info->order->shipping_state }},
                                {{ $case_order_info->order->shipping_country }},
                                {{ $case_order_info->order->shipping_zipcode }},
                                {{ $case_order_info->order->shipping_phone }}
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

                                    @foreach($case_order_info->order->items as $item)
                                        <tr>
                                            <td>{{ $item->sku }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ $item->price }}</td>
                                            <td>{{ $item->status_text }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <div class="row">
                            <div class="col-lg-6">
                                <strong>创建时间</strong>: {{ $case_order_info->order->create_time }}
                            </div>
                            <div class="col-lg-6">
                                <strong>支付方式</strong>: {{ $case_order_info->order->payment }}
                            </div>
                            <div class="col-lg-6">
                                <strong>支付时间</strong>: {{ $case_order_info->order->create_time }}
                            </div>
                        </div>
                    </div>
                </div>
                @foreach($case_order_info->order->packages as $package)
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Package:
                            <a href="{{ route('package.show', ['id' => $package->id]) }}" target="_blank">
                                <strong>#{{ $package->id }}</strong>
                            </a>
                            -
                            <strong>{{ $package->status_text }}</strong>
                        </div>
                        <div class="panel-body">
                            @if($package->shipping)
                                <div class="row form-group">
                                    <div class="col-lg-6">
                                        <strong>物流</strong>:
                                        <a href="{{ $package->shipping->url }}" target="_blank">
                                            {{ $package->shipping->type }}
                                        </a>
                                    </div>
                                    <div class="col-lg-6">
                                        <strong>物流网址</strong>
                                        {{  $package->shipping->url }}
                                    </div>
                                    <div class="col-lg-6">
                                        <strong>追踪号</strong>: {{ $package->tracking_no }}
                                    </div>
                                </div>
                            @endif
                            <div class="row form-group">
                                <div class="col-lg-6">
                                    <strong>创建</strong>: {{ $package->created_at }}
                                </div>
                                <div class="col-lg-6">
                                    <strong>打印</strong>: {{ $package->printed_at }}
                                </div>
                            </div>
                            <div class="row form-group">
                                <div class="col-lg-6">
                                    <strong>发货</strong>: {{ $package->shipped_at }}
                                </div>
                                <div class="col-lg-6">
                                    <strong>妥投</strong>:
                                    @if($package->delivered_at)
                                        {{ $package->delivered_at }}
                                        {{--({{ $package->delivery_age }}天)--}}
                                    @else
                                        --
                                    @endif
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
                                        @foreach($package->items as $item)
                                            <tr>
                                                <td>{{ $item->item->sku }}</td>
                                                <td>{{ $item->quantity }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">追踪信息</div>
                                        <div class="panel-body">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    {{ $package->latest_trackinginfo ? $package->latest_trackinginfo : '暂无追踪信息' }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="panel-footer">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <strong>更新时间</strong>: {{ $package->updated_at }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</div>