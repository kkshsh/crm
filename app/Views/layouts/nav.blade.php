<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            <a class="navbar-brand" href="/"><span class="fa fa-coffee1" aria-hidden="true"></span>
                Crm2.0.beta
            </a>
        </div>
		
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                @foreach(config('navigation') as $navigation)
					
                    @if(isset($navigation['subnavigations']))
						@if(request()->user()->group == 'super' || request()->user()->group == 'leader' || request()->user()->group == 'staff')
                        <li class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="javascript:">
                                <span class="glyphicon glyphicon-{{ $navigation['icon'] }}"></span>
                                {{ $navigation['name'] }}
                                <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">
                                @foreach($navigation['subnavigations'] as $subnavigation)
                                    <li>
                                        <a href="{{ route($subnavigation['url']) }}">
                                            <span class="glyphicon glyphicon-{{ $subnavigation['icon'] }}"></span>
                                            {{ $subnavigation['name'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
						@endif
                    @else
                        <li>
                            <a href="{{ route($navigation['url']) }}">
                                <span class="glyphicon glyphicon-{{ $navigation['icon'] }}"></span>
                                {{ $navigation['name'] }}
                                @if($navigation['url']=='message.index')
                  {{--                  @if(\App\Models\MessageModel::where('status','UNREAD')->count() > 0)
                                        <span class="badge">{{ \App\Models\MessageModel::where('status','UNREAD')->count() }}</span>
                                    @endif--}}
                                @endif
                                @if($navigation['url']=='messageReply.index')
                                    @if(\App\Models\Message\ReplyModel::where('status','NEW')->count() > 0)
                                        <span class="badge" style="color: red;">{{ \App\Models\Message\ReplyModel::whereIn('status',['NEW','FAIL'])->count() }}</span>
                                    @endif
                                @endif
                            </a>
                        </li>
                    @endif
					
                @endforeach
            </ul>

            <ul class="nav navbar-nav navbar-right">
{{--                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="#">换色</a></li>
                        <li role="separator" class="divider"></li>
                        <li><a href="#">系统</a></li>
                    </ul>
                </li>--}}
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <span class="glyphicon glyphicon-user" aria-hidden="true">{{request()->user()->name}}</span>

                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="#">修改密码</a></li>
                        <li><a href="/auth/logout">注销</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>