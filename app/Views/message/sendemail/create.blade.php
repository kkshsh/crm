@extends('common.form_no')
@section('formBody')

    <div class="panel panel-primary">
        <div class="panel-body">
            <form action="{{ route('sendemail.saveFile') }}" method="POST" enctype="multipart/form-data" kai="11111" id="sendemailSave">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <select class="form-control" onchange="changeChildren($(this));">
                                <option>请选择一级类型</option>
                                @foreach($parents as $parent)
                                    <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <select class="form-control" id="children" onchange="changeTemplateType($(this));">
                                <option>请选择类型</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <select class="form-control" id="templates" onchange="changeTemplate($(this));">
                                <option>请选择模版</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-2" id="loadingDiv">
                        <img src="{{ asset('loading.gif') }}" width="30"/>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                    <div>to</div>
                        <div class="form-group">
                            <input type="text" class="form-control" name="to" value="service@choies.com" disabled="true"/>
                        </div>
                    </div>
                    <div class="col-lg-6">
                    <div>to_email</div>
                        <div class="form-group">
                            <input type="email" class="form-control" name="to_email" value=""/>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                    <div>title</div>
                        <div class="form-group">
                            <input type="text" class="form-control" name="title" value=""/>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12" id="templateContent">
                        <div class="form-group">
                            <textarea class="form-control" id="editor" rows="16" name="content" style="width:100%;height:400px;">{{ old('content') }}</textarea>
                        </div>
                    </div>
                </div>
                <script type="text/javascript" charset="utf-8"> var um = UM.getEditor('editor'); </script>
                <div id="filehtml">
                    附件：<input id="upfile" type="file" name="upfile[1]" />
                    <input id="upfile" type="file" name="upfile[2]" />

                </div>
                <input type="button" name="addfile" value="继续追加附件" onclick="addFileHtml();" id="addfileBtn"/><br><br>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Save保存并发送</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop
@section('pageJs')
    <script type="text/javascript">
        function changeChildren(parent) {
            $('#loadingDiv').show();
            $('#children').html('');
            $('#children').append('<option>请选择类型</option>');
            $('#templates').html('');
            $('#templates').append('<option>请选择</option>');
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

        function changeTemplateType(type) {
            $('#loadingDiv').show();
            $('#templates').html('');
            $('#templates').append('<option>请选择</option>');
            $.post(
                    '{{ route('messageTemplateType.ajaxGetTemplates') }}',
                    {id: type.val()},
                    function (response) {
                        if (response != 'error') {
                            $.each(response, function (n, template) {
                                $('#templates').append('<option value="' + template.id + '">' + template.name + '</option>');
                            });
                        }
                    }, 'json'
            );
            $('#loadingDiv').hide();
        }

        function changeTemplate(template) {
            $('#loadingDiv').show();
            $.post(
                    '{{ route('messageTemplate.ajaxGetTemplate') }}',
                    {id: template.val()},
                    function (response) {
                        if (response != 'error') {
                            $('#templateContent').html('<div class="form-group"><textarea rows="16" name="content" style="width:100%;height:400px;">'+response.content+'</textarea></div>');
                        }
                        $('#loadingDiv').hide();
                    }, 'json'
            );
        }

        function addFileHtml(){
            var form=document.getElementById('sendemailSave');
            var count=0;
            for(var i=0;i<form.elements.length;i++){
                var name=form.elements[i].name;
                if(name.indexOf("file")>-1)
                {
                    count++;
                }
            }
            var more =document.getElementById('filehtml');
            var input = document.createElement("input");
            input.type = "file";
            input.name = "upfile["+count+"]";
            more.appendChild(input);
        }
    </script>
@stop