@extends('layouts.default')
@section('content')
    <div class="row">
        <div class="col-lg-6">
            @include('message.process.content')
            @include('message.process.reply')
        </div>
        <div class="col-lg-6">
            @if($message->related == 1)
                @include('message.process.orders_erp')
            @else
                @include('message.process.relate')
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
                            <strong>创建时间</strong>: {{ $message->created_at }}
                        </div>
                        <div class="col-lg-6">
                            <strong>更新时间</strong>: {{ $message->updated_at }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@section('pageJs')
    <script type="text/javascript">


        $(document).on("click", '.btn-translation', function () {
            text        = changeSome($(this).attr('need-translation-content'),1);
            content_key = $(this).attr('content-key');

            $.ajax({
                url: "{{route('ajaxGetTranInfo')}}",
                data: 'content=' + text,
                type: 'POST',
                dataType: 'JSON',
                success: function (data) {

                    if(data.content){
                        $('#content-'+content_key).text(data.content);
                    }else{
                        $('#content-'+content_key).text('翻译失败');
                    }
                }
            });
        });



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

        function changeTemplate(template, channelName) {
            $('#loadingDiv').show();
            $.post(
                    '{{ route('messageTemplate.ajaxGetTemplate') }}',
                    {id: template.val()},
                    function (response) {
                        var messgae_id="{{ $message->id }}";
                        var assign_name="{{ $message->assigner->name_en }}";
                        if (response != 'error') {

                            if(channelName == 'Amazon'){
                                //替换字符串
                                response['content'] = response['content'].replace(/<br>/g,"/n");
                                response['content'] = response['content'].replace("<br>", "/n");
                                response['content'] = response['content'].replace("署名", assign_name);
                                editor.setContent(response['content']);
                            }else{
                                $('#textcontent').val(response['content']);

                            }

                            /*
                            $('#templateContent').html('<div class="form-group"><textarea rows="16" name="content" style="width:100%;height:400px;">'+response['content']+'</textarea></div>');
                            */
                            //记录回复邮件的类型
                            $('#tem_type').val(response.type_id);
                        }
                        $('#loadingDiv').hide();
                    }, 'json'
            );
        }

        function getTransInfo(content) {
            $.ajax({
                url: "{{route('ajaxGetTranInfo')}}",
                data: 'content=' + content,
                type: 'POST',
                dataType: 'JSON',
                success: function (data) {
                    return data.content ? data.content : false;
                }
            });
        }

        function changeSome(text,type){
            if(type==1){

                text=text.replace(/\?/g, "^");
            }
            if(type==2){
                text=text.replace(/\^/g, "?");
            }

            return text;
        }

    </script>
@stop