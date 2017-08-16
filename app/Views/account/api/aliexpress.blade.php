<div class="modal fade" id="myModal{{ $account->id }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('channelAccount.updateApi', ['id' => $account->id]) }}" method="POST">
                {!! csrf_field() !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">{{ $account->alias }} API设置</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-lg-12">
                            <label for="account" class='control-label'>Aliexpress_member_id</label>
                            <small class="text-danger glyphicon glyphicon-asterisk"></small>
                            <input type='text' class="form-control" id="aliexpress_member_id" name='aliexpress_member_id' value="{{ $account->aliexpress_member_id }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-lg-12">
                            <label for="account" class='control-label'>Aliexpress_appkey</label>
                            <small class="text-danger glyphicon glyphicon-asterisk"></small>
                            <input type='text' class="form-control" id="aliexpress_appkey" name='aliexpress_appkey' value="{{ $account->aliexpress_appkey }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-lg-12">
                            <label for="account" class='control-label'>Aliexpress_appsecret</label>
                            <small class="text-danger glyphicon glyphicon-asterisk"></small>
                            <input type='text' class="form-control" id="aliexpress_appsecret" name='aliexpress_appsecret' value="{{ $account->aliexpress_appsecret }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-lg-12">
                            <label for="account" class='control-label'>Aliexpress_returnurl</label>
                            <small class="text-danger glyphicon glyphicon-asterisk"></small>
                            <input type='text' class="form-control" id="aliexpress_returnurl" name='aliexpress_returnurl' value="{{ $account->aliexpress_returnurl }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-lg-12">
                            <label for="account" class='control-label'>Aliexpress_refresh_token</label>
                            <small class="text-danger glyphicon glyphicon-asterisk"></small>
                            <input type='text' class="form-control" id="aliexpress_refresh_token" name='aliexpress_refresh_token' value="{{ $account->aliexpress_refresh_token }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-lg-12">
                            <label for="account" class='control-label'>Aliexpress_access_token</label>
                            <small class="text-danger glyphicon glyphicon-asterisk"></small>
                            <input type='text' class="form-control" id="aliexpress_access_token" name='aliexpress_access_token' value="{{ $account->aliexpress_access_token }}">
                        </div>
                    </div>


                    <div class="row">
                        <div class="form-group col-lg-12">
                            <label for="account" class='control-label'>Aliexpress_access_token_date</label>
                            <small class="text-danger glyphicon glyphicon-asterisk"></small>
                            <input id="aliexpress_access_token_date" class='form-control' name='aliexpress_access_token_date' type="text" placeholder='token过期日期' value="{{ $account->aliexpress_access_token_date }}">
                        </div>
                    </div>


                   {{-- <div class='form-group col-lg-3'>
                        <label for="expected_date">期望上传日期</label>

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

<link rel="stylesheet" href="{{ asset('css/jquery.cxcalendar.css') }}">
<script type='text/javascript'>
    $(document).ready(function(){
        $('#aliexpress_access_token_date').cxCalendar("YY-MM-DD hh:ss:ii");
    });
</script>