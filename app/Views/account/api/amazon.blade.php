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
                            <label for="account" class='control-label'>G-MAIL MESSAGE_SECRET</label>
                            <small class="text-danger glyphicon glyphicon-asterisk"></small>
                            <textarea class="form-control" rows="3" name="secret">{{ $account->secret }}</textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-lg-12">
                            <label for="account" class='control-label'>G-MAIL MESSAGE_TOKEN</label>
                            <small class="text-danger glyphicon glyphicon-asterisk"></small>
                            <textarea class="form-control" rows="3" name="token">{{ $account->token }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>