@extends('common.form')
@section('formAction') {{ route('messageTemplate.store') }} @stop
@section('formBody')
    <div class="row">
        <div class="form-group col-lg-6">
            <label for="type" class='control-label'>类型</label>
            <small class="text-danger glyphicon glyphicon-asterisk"></small>
            <div class="row">
                <div class="col-lg-4">
                    <select class="form-control" onchange="changeChildren($(this));">
                        <option value="">请选择一级类型</option>
                        @foreach($parents as $parent)
                            <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-6">
                    <select class="form-control" name="type_id" id="children">
                        <option value="">请选择类型</option>
                    </select>
                </div>
                <div class="col-lg-2" id="loadingDiv">
                    <img src="{{ asset('loading.gif') }}" width="30"/>
                </div>
            </div>
        </div>
        <div class="form-group col-lg-6">
            <label for="name" class='control-label'>名称</label>
            <small class="text-danger glyphicon glyphicon-asterisk"></small>
            <input type='text' class="form-control" id="name" placeholder="模版名称" name='name' value="{{ old('name') }}">
        </div>
    </div>
    <div class="row">
        <div class="form-group col-lg-12">
            <label for="content" class='control-label'>模版</label>
            <small class="text-danger glyphicon glyphicon-asterisk"></small>
            <textarea class="form-control" name="content" rows="16">{{ old('content') }}</textarea>
        </div>
    </div>
@stop
@section('pageJs')
    <script type="text/javascript">
        function changeChildren(parent) {
            $('#loadingDiv').show();
            $('#children').html('');
            $('#children').append('<option>请选择类型</option>');
            if (parent.val() != "") {
                $.post(
                        '{{ route('messageTemplateType.ajaxGetChildren') }}',
                        {id: parent.val()},
                        function (response) {
                            if (response != 'error') {
                                $.each(response, function (n, child) {
                                    $('#children').append('<option value="' + child.id + '">' + child.name + '</option>');
                                });
                            }
                        }, 'json'
                );
            }
            $('#loadingDiv').hide();
        }
    </script>
@stop