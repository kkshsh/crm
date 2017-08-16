@if (count($errors) > 0)
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@if($message->related)
    <div class="panel panel-primary">
        <div class="panel-heading"><strong>回复:</strong></div>
        <div class="panel-body">
            <form action="{{ route('message.reply', ['id'=>$message->id]) }}" method="POST" onsubmit="return check()" ;>
                {!! csrf_field() !!}
                <input type="hidden" id="reference_url" name="reference_url" value="">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <select class="form-control select_select0" onchange="changeChildren($(this));">
                                <option>请选择一级类型</option>
                                @foreach($parents as $parent)
                                    <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                @endforeach
                            </select>

                            <div class="topNavList">
                                @foreach($parents as $parent)
                                    <a href="#" id="{{ $parent->id }}"
                                       value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</a>
                                @endforeach
                            </div>
                            <br/>
                            <br/>
                            <div style="width:800px;">
                                <div class="subNav" style="float: left">
                                </div>
                                <div class="subNav1" style="float: left;margin-left:20px;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <script type="text/javascript">
                        $(".topNavList a").hover(function () {
                            var parent_id = this.id;
                            $.post(
                                    '{{ route('messageTemplateType.ajaxGetChildren') }}',
                                    {id: parent_id},
                                    function (response) {
                                        $('.subNav').html("");
                                        if (response != 'error') {
                                            $.each(response, function (n, child) {
                                                $('.subNav').append('<a id="' + child.id + '">' + child.name + '</a><br/><br/>');
                                            });
                                        }

                                    }, 'json'
                            );
                            return false;
                        });

                        $(".subNav").on('mouseover', 'a', function () {
                            var parent_id1 = this.id;
                            $.post(
                                    '{{ route('messageTemplateType.ajaxGetTemplates') }}',
                                    {id: parent_id1},
                                    function (response) {
                                        $('.subNav1').html("");
                                        if (response != 'error') {
                                            $.each(response, function (n, template) {
                                                $('.subNav1').append('<a id="' + template.id + '">' + template.name + '</a><br/>');
                                            });
                                        }
                                    }, 'json'
                            );
                        });

                        $(".subNav1").on('mouseover', 'a', function () {
                            var parent_id1 = this.id;
                            $.post(
                                    '{{ route('messageTemplate.ajaxGetTemplate') }}',
                                    {id: parent_id1},
                                    function (response) {
                                        var messgae_id = "{{ $message->id }}";
                                        var assign_name = "{{ $message->assigner->name_en }}";
                                        if (response != 'error') {
                                            //替换字符串
                                            response['content'] = response['content'].replace(/<br>/g,"/n");
                                            response['content'] = response['content'].replace("<br>", "/n");
                                            response['content'] = response['content'].replace("署名", assign_name);
                                            editor.setContent(response['content']);
                                            /*
                                            $('#templateContent').html('<div class="form-group"><textarea rows="16" name="content" style="width:100%;height:400px;">' + response['content'] + '</textarea></div>');
                                            */
                                            //记录回复邮件的类型
                                            $('#tem_type').val(response.type_id);
                                        }
                                        $('#loadingDiv').hide();
                                    }, 'json'
                            );
                        });

                    </script>
                    <script>
                        function getIframeContent(frameId){
                            var frameObj = document.getElementById(frameId);
                            var frameContent = frameObj.contentWindow.document.body.innerHTML;
                            var iframe=frameContent.substr(0, 100);
                            var substr = iframe.match(/邮箱:(\S*)<br>/);
                            var user_email=substr[1];
                            $("#to_email").val(user_email);
                            $("#to_name").val(user_email);
                            $("#select_email").val(user_email);
                        }
                    </script>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <select class="form-control select_select0" id="children" onchange="changeTemplateType($(this));">
                                <option>请选择类型</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <select class="form-control select_select0" id="templates" onchange="changeTemplate($(this), '{{$message->channel_name}}');">
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
                        <div class="form-group">
                            <input type="text" id="to_name" class="form-control" name="to" value="{{ $message->from_name }}"/>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <input type="text" id="to_email" class="form-control" name="to_email"
                                   value="{{ $message->from }}"/>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <input type="text" class="form-control" name="title" value="Re: {{ $message->subject }}"/>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="updatefile_id" name="updatefile" value=""/>
                <input type="hidden" id="smt_return_img_id" name="smt_return_img" value=""/>
                <?php
                if($message->from=="pre-sale@choies.com"){
                ?>
                <iframe style="display:none;" id="content_email" class="embed-responsive-item" src="{{ route('message.content', ['id'=>$message->id]) }}"></iframe>
                <a href="javascript:void(0);" onclick="getIframeContent('content_email');">获取用户提交的邮箱并自动关联邮箱</a>
                <?php
                }
                ?>
                <div class="row">
                    <div class="col-lg-12" id="templateContent">
                        <div class="form-group">
                            <textarea class="form-control" rows="16" name="content" @if($message->channel->api_type == 'amazon') id="editor" @else id="textcontent" @endif
                                      style="width:100%;height:400px;"></textarea>
                        </div>
                    </div>
                </div>
                <script type="text/javascript" charset="utf-8">
                    var editor = UM.getEditor('editor');
                    function setImg(id) {
                        var value = $('#textcontent').val();
                        $('#textcontent').val(value + " /:" + id.replace('ali_', ''));
                    }
                </script>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <button id="save" type="submit" class="btn btn-primary">邮件归类选择</button>
                            <select class="form-control select_select0" name="type_id1">
                                <option value="0">请选择一级类型</option>
                                @foreach($parent_type as $parenttype)
                                    <option value="{{ $parenttype->id }}">{{ $parenttype->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <button id="save22" type="submit" class="btn btn-primary">回复</button>
                        </div>

                        @if($message->channel_name == 'Aliexpress')
                        <div style="float: right;">
                            <?php
                            for ($i = 0; $i < 54; $i++) {
                                echo '<img class="aliimg" id="ali_' . str_pad($i, 3, "0", STR_PAD_LEFT) . '" onClick="setImg(this.id)" width="20" src="http://i02.i.aliimg.com/wimg/feedback/emotions/' . $i . '.gif" />&nbsp;';
                                if (($i + 1) % 9 == 0) echo '<br />';
                            }
                            ?>
                        </div>
                        @endif
                    </div>
                </div>
                <input type="hidden" id="tem_type" name="type_id">
            </form>
            @if($message->channel->api_type == 'aliexpress')
                <form action="{{ route('message.updateimg', ['id'=>$message->id]) }}" target="hidden_frame" enctype="multipart/form-data" method="post"
                      name="uploadfile">上传图片[图片格式JPG，大小不超过200KB]：<input id="upfile" type="file" name="upfile" /><br>
                    <input type="submit" value="上传" />
                </form>
            @endif
            @if($message->channel->api_type == 'amazon')
            <form action="{{ route('message.updatefile') }}" target="hidden_frame" enctype="multipart/form-data" method="post"
                  name="uploadfile">上传文件：<input id="upfile" type="file" name="upfile" /><br>
                <input type="submit" value="上传" />
            </form>
            @endif
            <iframe style="display:none" name='hidden_frame' id="hidden_frame"></iframe>
            <div class="fromtrs">
                <div id="uploadok"></div>
                <a id="href"/>
            </div>
            <script type="text/javascript">
                function callback(message,success,smt_return_img)
                {
                    if(success==false)
                    {
                        $("#uploadok").html("上传失败");
                    }
                    else{
                        $("#uploadok").html("上传成功");
                        $("#href").html(message);
                        $("#updatefile_id").val("http://52.78.109.226"+ message);
                        $("#smt_return_img_id").val(smt_return_img);
                    }
                }
            </script>
        </div>
    </div>
@endif
<script>
    function check() {
        var referrer = document.referrer;
        $("#reference_url").val(referrer);
        var to_email = $("#to_email").val();
        //对电子邮件的验证
        if (!to_email.indexOf("@")) {
            alert('提示\n\n请输入有效的E_mail！');
            return false;
        }
        return true;
    }
</script>
<div class="panel panel-primary">
    <div class="panel-heading"><strong>操作</strong></div>
    <div class="panel-body">
        <div class="row form-group">
            <div class="col-lg-6">
                <form action="{{ route('message.assignToOther', ['id'=>$message->id]) }}" method="POST">
                    {!! csrf_field() !!}
                    <div class="input-group">
                        <select class="form-control select_select0" name="assign_id">
                            <option>请选择</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ ($message->last and ($user->id == $message->last->assign_id)) ? 'selected' : '' }}>
                                    {{ ($message->last and ($user->id == $message->last->assign_id)) ? '历史客服: ' : '' }}{{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="input-group-btn">
                            <button class="btn btn-success" type="submit">
                                <span class="glyphicon glyphicon-random"></span> 转交
                            </button>
                        </span>
                    </div>
                </form>

            </div>
            <div class="col-lg-6 text-right">
                <button class="btn btn-warning" type="button"
                        onclick="if(confirm('确认无需回复?')){location.href='{{ route('message.notRequireReply', ['id'=>$message->id]) }}'}">
                    <span class="glyphicon glyphicon-minus-sign"></span> 无需回复
                </button>
                <button class="btn btn-warning" type="button"
                        onclick="if(confirm('确认稍后处理?')){location.href='{{ route('message.dontRequireReply', ['id'=>$message->id]) }}'}">
                    <span class="glyphicon glyphicon-minus-sign"></span> 稍后处理
                </button>
            </div>
        </div>
        <div class="panel panel-success">
            <div class="panel-heading">转发邮件</div>
            <div class="panel-body">
                <form action="{{ route('message.foremail', ['id'=>$message->id]) }}" method="POST">
                    {!! csrf_field() !!}
                    <div class="row form-group">
                        <div class="col-lg-8">
                            <div id="change">
                                <select class="form-control select_select0" name="email">
                                    <option>请选择</option>
                                    @foreach($emailarr as $email_name)
                                        <option value="{{ $email_name }}">
                                            {{ $email_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="radio" name="payMethod" value="1" checked/>请选择
                            <input type="radio" name="payMethod" value="2"/>自定义
                            <input type="hidden" name="content"
                                   value="<iframe class='embed-responsive-item' src='{{ route('message.content', ['id'=>$message->id]) }}'></iframe>"/>
                        </div>
                        <div class="col-lg-4">
                            <button type="submit" class="btn btn-success">
                                <span class="glyphicon glyphicon-search"></span> 转发
                            </button>
                        </div>
                    </div>
                </form>
                <script type="text/javascript">
                    $(function () {
                        $(":radio").click(function () {
                            var str = "";
                            if ($(this).val() == 1) {
                                str = "<select class='form-control select_select0' name='email'>";
                                str += "<option>请选择</option>";
                                @foreach($emailarr as $email_name)
                           str += "<option value='{{ $email_name }}'>{{ $email_name }}</option>"
                                @endforeach
                           str += "</select>";
                                $("#change").html(str);
                            } else {
                                str = "<input class='form-control' type='text' name='email' >";
                                $("#change").html(str);
                            }
                        });
                    });
                </script>
            </div>
        </div>
        <script type="text/javascript">
            window.onload = function () {
                // 加载页面时判断是否有数据并加载\localStorage["a"]
                var kai = $("#editor").val();
                var content1 = localStorage["http_crm_jinjidexiaoxuesheng_com_message_processeditor-drafts-data"];
                if (!window.localStorage) {
                    console.log("error");
                } else {
                    if (localStorage["http_crm_jinjidexiaoxuesheng_com_message_processeditor-drafts-data"] != null) {
                        editor.setContent(content1)
                        //$("#editor").val(content1)
                    } else {
                        $("#editor").val("");
                    }
                }
            };
            // 点击发表时删除数据
            document.getElementById("save22").onclick = function () {
                editor.value = "";
                if (!window.localStorage) {
                    UserData.remove('http_crm_jinjidexiaoxuesheng_com_message_processeditor-drafts-data');
                } else {
                    localStorage.removeItem('http_crm_jinjidexiaoxuesheng_com_message_processeditor-drafts-data');
                }
            };
        </script>
        @if(request()->session()->get('workflow')=='keeping')
            <div class="row">
                <div class="col-lg-12">
                    <button class="btn btn-danger" type="button"
                            onclick="if(confirm('确认终止工作流?')){location.href='{{ route('message.endWorkflow', ['id'=>$message->id]) }}'}">
                        <span class="glyphicon glyphicon-minus-sign"></span> 终止工作流
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>