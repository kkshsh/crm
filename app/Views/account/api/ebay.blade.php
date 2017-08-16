
<div class="modal fade" id="myModal{{ $account->id }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('channelAccount.updateApi', ['id' => $account->id]) }}" method="POST">
                {!! csrf_field() !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">{{ $account->account }} API设置</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-lg-12">
                            <label for="account" class='control-label'>Ebay开发者账号</label>
                            <small class="text-danger glyphicon glyphicon-asterisk"></small>
                            <input type='text' class="form-control" id="ebay_developer_account" name='ebay_developer_account'
                                   value="{{ $account->ebay_developer_account }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-lg-12">
                            <label for="account" class='control-label'>Ebay开发者账号DEVID</label>
                            <small class="text-danger glyphicon glyphicon-asterisk"></small>
                            <input type='text' class="form-control" id="ebay_developer_devid" name='ebay_developer_devid'
                                   value="{{ $account->ebay_developer_devid }}">
                        </div>
                    </div>


                    <div class="row">
                        <div class="form-group col-lg-12">
                            <label for="account" class='control-label'>Ebay开发者账号APPID</label>
                            <small class="text-danger glyphicon glyphicon-asterisk"></small>
                            <input type='text' class="form-control" id="ebay_developer_appid" name='ebay_developer_appid'
                                   value="{{ $account->ebay_developer_appid }}">
                        </div>
                    </div>



                    <div class="row">
                        <div class="form-group col-lg-12">
                            <label for="account" class='control-label'>Ebay开发者账号CERTID</label>
                            <small class="text-danger glyphicon glyphicon-asterisk"></small>
                            <input type='text' class="form-control" id="ebay_developer_certid" name='ebay_developer_certid'
                                   value="{{ $account->ebay_developer_certid }}">
                        </div>
                    </div>


                    <div class="row">
                        <div class="form-group col-lg-12">
                            <label for="account" class='control-label'>EbayToken</label>
                            <small class="text-danger glyphicon glyphicon-asterisk"></small>
                            <textarea class="form-control" rows="3" name="ebay_token">{{ $account->ebay_token  }}</textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-lg-12">
                            <label for="account" class='control-label'>EbayEub账号</label>
                            <small class="text-danger glyphicon glyphicon-asterisk"></small>
                            <input type='text' class="form-control" id="ebay_eub_developer" name='ebay_eub_developer'
                                   value="{{ $account->ebay_eub_developer }}">
                        </div>
                    </div>

{{--                    <div class="row">
                        <div class="form-group col-lg-5" style="clear:left;">
                            <label for="country_id" class="control-label">已存Paypal</label>
                            <select name="country_id" class="form-control" multiple style="height:250px;width:200px;">
                                @foreach($paypals as $paypal)
                                    <option class="form-control" value="{{ $paypal->id }}" onclick="addOption( this )">
                                        {{ $paypal->paypal_email_address }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-lg-5" style="clear:right;">
                            <label for="addNewOption" class="control-label">已选Paypal(可多选,用于校验订单paypal)</label>
                            <small class="text-danger glyphicon glyphicon-asterisk"></small>
                            <select class="form-control" id="addNewOption" multiple style="height:250px;width:200px;">
                                @foreach($account->paypal as $paypal)
                                    <option class="form-control selectedOption" value="{{ $paypal->id  }}" onclick="deleteOption( this )">{{ $paypal->paypal_email_address }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="display:none;">
                            <textarea class="form-control" rows="3" type="hidden" id="paypal_ids" name='paypal_ids' readonly></textarea>
                        </div>
                    </div>--}}

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        getPostOption();
    });

    function getPostOption() {
        var selectedOptions = "";
        $(".selectedOption").each(function () {
            selectedOptions += $.trim($(this).val()) + ",";
        });
        selectedOptions = selectedOptions.substring(0, selectedOptions.length - 1);
        $("#paypal_ids").html(selectedOptions);
    }

    function addOption(that) {
        if (!$(that).hasClass("selected")) {
            $(that).addClass("selected");
            var optionHtml = '<option class="form-control selectedOption" value="' + $(that).val() + '" onclick="deleteOption( this )">' + $(that).html() + '</option>';
            $("#addNewOption").append(optionHtml);
            getPostOption();
        }
    }

    function deleteOption(that) {
        $(that).remove();
        getPostOption();
    }
</script>