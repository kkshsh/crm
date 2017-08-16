<div class="list-group">
    @foreach(config('navigation') as $key => $navigation)
        <a class="list-group-item {{ Request::segment(1) === $navigation['location'] ? 'active' : '' }}" id="nav{{ $key+1 }}" data-toggle="collapse" href="#subNav{{ $key+1 }}">
            <span class="glyphicon glyphicon-{{ $navigation['icon'] }}"></span>
            {{ $navigation['name'] }}
        </a>
        @if(isset($navigation['subnavigations']))
            <div kai="123" class="collapse list-group {{ Request::segment(1) === $navigation['location'] ? 'in' : '' }}" id="subNav{{ $key+1 }}">
                @foreach($navigation['subnavigations'] as $subnavigation)
                    <a class="list-group-item {{ Request::segment(1) === $navigation['location'] && Request::segment(2) === $subnavigation['location'] ? 'list-group-item-info' : '' }}" href="{{ route($subnavigation['url']) }}">
                        <span class="glyphicon glyphicon-option-horizontal"></span>
                        {{ $subnavigation['name'] }}
                    </a>
                @endforeach
            </div>
        @endif
    @endforeach
</div>