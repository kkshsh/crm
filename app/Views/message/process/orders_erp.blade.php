@if(count($erp_apiOrderinfo)>0)
    <ul class="nav nav-tabs" role="tablist">
        @foreach($erp_apiOrderinfo as $key=> $relatedOrder)
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
@if(count($erp_apiOrderinfo)>0)
    <div class="tab-content">
        @foreach($erp_apiOrderinfo as $key => $relatedOrder)
            <div class="tab-pane {{ $key == 0 ? 'active' : '' }}" role="tabpanel" id="{{ $relatedOrder['ordernum'] }}">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        订单:
                        {{--<a href="http://ws.jinjidexiaoxuesheng.com/admin/workstation/order/{{ $relatedOrder['id'] }}" target="_blank">--}}
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
                                <a href="javascript:void(0);" onclick="if(confirm('确认取消订单: {{ $relatedOrder['ordernum'] }} ?')){location.href='{{ route('message.cancelRelatedOrder', ['id'=>$message->id,'relatedOrderId'=>$relatedOrder['msg_orders_id']]) }}'}">
                                    <small class="glyphicon glyphicon glyphicon-off"></small>
                                </a>
                            </div>
                    </div>

                    <div class="panel-body">
                        <div class="row form-group">
                            <div class="col-lg-3">
                                <strong>总额</strong>: {{ $relatedOrder['amount'] }} {{ $relatedOrder['currency'] }}
                            </div>
                            <div class="col-lg-3">
                                <strong>产品</strong>: {{ $relatedOrder['amount_product'] }} {{ $relatedOrder['currency'] }}
                            </div>
                            <div class="col-lg-3">
                                <strong>运费</strong>: {{ $relatedOrder['amount_shipping'] }} {{ $relatedOrder['currency'] }}
                            </div>
                            {{--<div class="col-lg-3">--}}
                            {{--<strong>促销</strong>: {{ $relatedOrder->order->amount_coupon }} {{ $relatedOrder->order->currency }}--}}
                            {{--</div>--}}
                            {{--<div class="col-lg-3">--}}
                            {{--<strong>红人</strong>: {{ $relatedOrder->order->comment1 }}--}}
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
                                <table class="table table-bordered" id="package_item">
                                    <thead>
                                    <tr>
                                        <th>SKU</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>投诉类型</th>
                                        <th>投诉详情</th>
                                        <th>具体描述</th>
                                        <th>Package</th>
                                        <th id="package_item_th">退款金额</th>
                                    </tr>
                                    </thead>
                                    <?php $relatemessage = $relatedOrder['relatedMessage'];?>
                                    @foreach($relatedOrder['package_info'] as $package)
                                        @foreach($package['packageitem_info'] as $key1=>$item)
                                    {{--@foreach($relatedOrder['orderitem_info'] as $key1=>$item)--}}

                                            <tr>
                                            <td>{{ $item['sku'] }}</td>
                                            <td>{{ $item['qty'] }}</td>
                                            <td>{{ $item['price'] }}</td>
                                            <td>{{ $item['deleted'] }}</td>
                                            <td>
                                                <select class="form-control" id="1key_{{$key1}}" data="key_{{$key1}}" tousuname="{{$key1}}" onchange="changeTemplateType1($(this));">
                                                    <?php
                                                    if(isset($relatemessage[$key1]))
                                                    {

                                                        echo '<option value="">'.$relatemessage[$key1]['com'].'</option>';
                                                    }
                                                    else
                                                    {
                                                    ?>
                                                    @foreach($complaint_arr as $key=>$value)
                                                        <option value="{{$key}}">
                                                            <?php
                                                            if($key=="logistics"){
                                                                echo "物流问题";
                                                            }elseif($key=="sendwrong"){
                                                                echo "错发漏发";
                                                            }elseif($key=="quality"){
                                                                echo "质量问题";
                                                            }elseif($key=="sizewrong"){
                                                                echo "尺码问题";
                                                            }elseif($key=="picturewrong"){
                                                                echo "图货不一";
                                                            }elseif ($key=="other") {
                                                                echo "其他问题";
                                                            }elseif ($key=="normal") {
                                                                echo "正常";
                                                            }
                                                            ?>
                                                        </option>
                                                    @endforeach
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                            <td>
                                                <?php
                                                if(isset($relatemessage[$key1]))
                                                {

                                                    echo '<select class="form-control"><option value="">'.$relatemessage[$key1]['com_name'].'</option></select>';
                                                }
                                                else
                                                {
                                                ?>
                                                <select class="form-control" value="" id="key_{{$key1}}" kai="123_{{$key1}}" data="{{$key1}}" onchange="changeTemplateType2($(this));">
                                                    <option>正常</option>
                                                </select>
                                                <?php
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" data="{{$key1}}" placeholder="具体描述..." name="com_name" onblur="kai($(this));" value="<?php echo isset($relatemessage[$key1]) ? $relatemessage[$key1]['content'] : '';?>">
                                            </td>
                                            <td>{{ $item['packageid'] }}</td>

                                            <td>
                                                <input type="number" class="form-control" data="{{$key1}}" placeholder="请输入退款金额..." id="refund_amount{{$key1}}" name="refund_amount{{$key1}}" onblur="kai_refund($(this));" value="<?php echo isset($relatemessage[$key1]) ? $relatemessage[$key1]['refund_amount'] : '';?>">
                                            </td>

                                            <input type="hidden" id="ordernum_id" value="{{ $relatedOrder['ordernum'] }}">
                                            <input type="hidden" id="email_id" value="{{ $relatedOrder['email'] }}">
                                            <input type="hidden" id="aa{{$key1}}" value="{{ $item['sku'] }},{{ $item['qty'] }},{{ $item['packageid'] }}" sku="{{ $item['sku'] }}" qty="{{ $item['qty'] }}" price="{{ $item['price'] }}" packageid="{{ $item['packageid'] }}" refund_amount=""  com="正常" comname="正常" tousucontent="null"/>
                                        </tr>
                                        @endforeach
                                    @endforeach
                                </table>
                                <script type="text/javascript">
                                    function changeTemplateType1(type){
                                        var key_id=type.attr("data");
                                        var keyid=type.attr("tousuname");
                                        $("#"+key_id).html("");
                                        var tousu_name=type.val();
                                        $.ajax({
                                            url:'/complaint/ajaxGetChildren1',
                                            type:'POST',
                                            dataType:'json',
                                            data:{comname: type.val()},
                                            success:function(res){
                                                $("#"+key_id).append('<option value="">请选择</option>');
                                                $.each(res, function (n, child) {
                                                    $("#"+key_id).append('<option value="' + n + '">' + child + '</option>');
                                                });
                                                $("#aa"+keyid).attr("com",tousu_name)
                                            }
                                        })
                                    }
                                    function changeTemplateType2(type){
                                        var sku_id=type.attr("data");
                                        var type_name=type.val();
                                        $("#aa"+sku_id).attr("comname",type_name);
                                        return false;
                                    }

                                    function changeTemplateType3(type){
                                        var packageid=type.attr("data");
                                        var type_name=type.val();
                                        $("#aa"+packageid).attr("packageid",type_name);
                                        return false;
                                    }

                                    function kai(type){
                                        var sku_id=type.attr("data");
                                        var tousucontent=type.val();
                                        $("#aa"+sku_id).attr("tousucontent",tousucontent);
                                        return false;
                                    }

                                    function kai_refund(type) {
                                        var refund_amount_id=type.attr("data");
                                        var refund_amount=type.val();
                                        if (typeof (refund_amount) != "undefined" && !isNaN(refund_amount)){
                                            var refund_amount_sum = "";
                                            var tr_sum = 0;
                                            var count_tb = $("#package_item").find("tr").length;
                                            for(var i=0;i<count_tb-1;i++){
                                                var refund_sum = $("#refund_amount"+i).val();
                                                if (typeof (refund_sum) != "undefined" && !isNaN(refund_sum)){
                                                    tr_sum = tr_sum*1 + refund_sum*1
                                                }
                                            }
                                            if (tr_sum>0){
                                                refund_amount_sum = (tr_sum*1).toFixed(2);
                                            }
                                            $("#refund").val(refund_amount_sum);
                                        }
                                        $("#aa"+refund_amount_id).attr("refund_amount",refund_amount);
                                        return false;
                                    }
                                </script>
                            </div>
                        </div>
                    </div>

                    <div class="panel-footer">
                        <div class="row">
                            <div class="col-lg-6">
                            <strong>创建时间</strong>: {{ $relatedOrder['create_time'] }}
                            </div>
                            <div class="col-lg-6">
                            <strong>支付方式</strong>: {{ $relatedOrder['payment'] }}
                            </div>
                            <div class="col-lg-6">
                            <strong>支付时间</strong>: {{ $relatedOrder['create_time'] }}
                            </div>
                        </div>
                    </div>
                </div>
                <!--投诉类型-->
                <div class="panel panel-success">
                    <div id="refund_status"></div>
                    <div class="panel-heading">
                        解决方案:
                    </div>
                    <div class="panel-body" id="complaint_status">
                        <form action="" class="bs-example bs-example-form" role="form">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="input-group">
                                        <div class="input-group-btn">
                                            <button type="button" class="btn btn-default
                                 dropdown-toggle" data-toggle="dropdown">
                                                解决方案:
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                                                @foreach($settled_arr as $key=>$value)
                                                    <li role="presentation" >
                                                        <a href="javascript:void(0);" class="points-on1" data-value="{{$value}}" role="menuitem" tabindex="-1">{{$value}}</a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div><!-- /btn-group -->
                                        <input type="text" class="form-control" id="settled" name="settled_name" value="">
                                    </div><!-- /input-group -->
                                    <br>
                                    <div class="form-group" id="tuikuan" style="display:none;">
                                        <label for="name">请输入退款金额(该订单币种是:{{ $relatedOrder['currency'] }})</label>
                                        <input type="text" class="form-control" id="refund" value="" readonly="readonly"
                                               placeholder="总退款金额">
                                        <input type="hidden" id="currency" value="{{ $relatedOrder['currency'] }}">
                                    </div>
                                </div><!-- /.col-lg-6 --><br><br>
                                <br>
                            </div><!-- /.row -->
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <?php
                                    if($relatedOrder['relatedCom']){
                                    if($relatedOrder['relatedCom']->ordernum == $relatedOrder['ordernum']){
                                        echo "该订单已提交过 提交内容如下:<br/>";
                                        echo $relatedOrder['relatedCom']->ws_return?$relatedOrder['relatedCom']->ws_return:"";
                                    }else{
                                    ?>
                                    <button id="complaint_save" type="submit" class="btn btn-primary" count="{{ count($relatedOrder['orderitem_info']) }}">提交</button>
                                    <?php
                                    }
                                    }else{
                                    ?>
                                    <button id="complaint_save" type="submit" class="btn btn-primary" count="{{ count($relatedOrder['orderitem_info']) }}">提交</button>
                                    <?php
                                    }
                                    ?>



                                </div>
                            </div>
                        </form>
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
                    (旧WS：<a href="http://ws.jinjidexiaoxuesheng.com:8888/admin/workstation/package/{{ $package['id'] }}" target="_blank">
                    <strong>#{{ $package['id'] }}</strong>
                    </a>)
                    -
                    <strong>{{ $package['status'] }}</strong>
                    </div>
                    @if(isset($package['shipping_name']) and $package['shipping_name'])
                    <div class="panel-body">
                    <div class="row form-group">
                    <div class="col-lg-6">
                    <strong>物流</strong>:
                    <a href="{{ $package['tracking_link'] }}" target="_blank">
                    {{ $package['shipping_name'] }}
                    </a>
                    </div>
                    <div class="col-lg-6">
                    <strong>物流网址</strong>
                    {{ $package['tracking_link'] }}
                    </div>
                    <div class="col-lg-6">
                    <strong>追踪号</strong>: {{ $package['tracking_no'] }}
                    </div>
                    </div>
                    <div class="row form-group">
                    <div class="col-lg-6">
                    <strong>创建</strong>: {{ $package['created'] }}
                    </div>
                    <div class="col-lg-6">
                    <strong>打印</strong>: {{ $package['print_time'] }}
                    </div>
                    </div>
                    <div class="row form-group">
                    <div class="col-lg-6">
                    <strong>发货</strong>: {{ $package['ship_time'] }}
                    </div>
                    <div class="col-lg-6">
                    <strong>妥投</strong>:
                    @if($package['delivery_time'])
                    {{ $package['delivery_time'] }}
                    ({{ $package['delivery_age'] }}天)
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
                    @foreach($package['packageitem_info'] as $item)
                    <tr>
                    <td>{{ $item['erp_sku'] }}</td>
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

<script type="text/javascript">
    $(document).ready(function(){
        $("#package_item tr td:nth-child(9)").hide();
        $("#package_item_th").hide();
    });
    $(function(){
        $('.points-on1').bind("click",function(){
            $("#refund_status").html("").css("color","green");
            var settled_name=$(this).attr('data-value');
            $("#settled").val(settled_name).css("color","");
            if(settled_name=="部分退款"){
                $("#tuikuan").show();
                $("#package_item tr td:nth-child(9)").show();
                $("#package_item_th").show();
            }else{
                $("#refund").val("");
                $("#tuikuan").hide();
                $("#package_item tr td:nth-child(9)").hide();
                $("#package_item_th").hide();
            }
        })
        $("#complaint_save").click(function(){
            $("#refund_status").html("").css("color","green");
            var settled_name=$("#settled").val();
            if(settled_name=="部分退款"){
                var refund=$("#refund").val();
                if(refund==''){
                    $("#refund_status").html("您选择了[部分退款]，请输入退款金额。").css("color","red");
                    return false;
                }
            }
            var count=$("#complaint_save").attr("count");
            var str="";
            for (var i = 0; i < count; i++) {
                str +=$("#aa"+i).attr("sku")+","+$("#aa"+i).attr("qty")+","+$("#aa"+i).attr("price")+","+$("#aa"+i).attr("com")+","+$("#aa"+i).attr("comname")+","+$("#aa"+i).attr("tousucontent")+","+$("#aa"+i).attr("packageid")+","+$("#aa"+i).attr("refund_amount")+";";
            };
            settled_name=$("#settled").val();
            ordernum_id=$("#ordernum_id").val();
            email_id=$("#email_id").val();
            currency=$("#currency").val();
            var refund=$("#refund").val();
            complaint_content=$("#complaint_content").val();
            $.ajax({
                url:'/complaint/save',
                type:'POST',
                dataType:'json',
                data:{str:str,settled_name:settled_name,currency:currency,refund:refund,message_id:"<?php echo $message->id;?>",ordernum:ordernum_id,email:email_id,assign_id:"<?Php echo request()->user()->id;?>"},
                success:function(res){
                    if(res.success==1){
                        $("#complaint_status").html(res.content).css("color","green");
                        return false;
                    }else{
                        $("#complaint_status").html(res.content).css("color","red");
                        return false;
                    }
                }
            })

            return false;
        })
    })
</script>