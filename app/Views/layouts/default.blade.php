@extends('layouts.base')
@section('meta')
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
@stop
@section('css')
    <link href="{{ asset('css/bootstrap.css') }}" rel="stylesheet">{{-- BOOTSTRAP CSS --}}
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">{{-- OUR CSS --}}
    <link href="{{ asset('ueditor/themes/default/css/umeditor.css') }}" rel="stylesheet">{{-- OUR CSS --}}
    <!--时间控件-->
    <link href="{{ asset('css/bootstrap-datetimepicker.css') }}" rel="stylesheet" media="screen">
    <link href="{{ asset('plugins/pace/dataurl.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet">

@stop
@section('js')
    {{--<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>--}}{{-- JQuery --}}
    <script src="{{ asset('js/jquery.min.js') }}"></script>{{-- JQuery JS --}}
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>{{-- BOOTSTRAP JS --}}
    <script src="{{ asset('js/uri.min.js') }}"></script>{{-- JQuery URI --}}

    <script src="{{ asset('js/jquery.cxcalendar.min.js') }}"></script>
    <!--编辑工具插件-->
    <script src="{{ asset('ueditor/umeditor.config.js') }}"></script>
    <script src="{{ asset('ueditor/umeditor.min.js') }}"></script>
    <script src="{{ asset('ueditor/lang/zh-cn/zh-cn.js') }}"></script>
    <!--时间控件-->
    <script type="text/javascript" src="{{ asset('js/bootstrap-datetimepicker.js') }}" charset="UTF-8"></script>
    <script src="{{ asset('js/lodash.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script src="{{ asset('plugins/pace/pace.min.js') }}"></script>
@stop
@section('init')
    <script type="text/javascript">
        {{-- CSRF token for AJAX --}}
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

@stop
@section('body')
    @include('layouts.nav')
    <div class="container-fluid main">
        @if(isset($sidebar))
            <div class="row">
                <div class="col-lg-2">
                    @include('layouts.sidebar')
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-lg-{{ isset($sidebar) ? '10' : '12' }}">
                <ol class="breadcrumb">
                    {{--<li><a href="/">主页</a></li>--}}
                    @section('breadcrumbs')
                        @if(isset($metas['mainTitle']))
                            <li><a href="{{ $metas['mainIndex'] }}">{{ $metas['mainTitle'] }}</a></li>
                        @endif
                        @if(isset($metas['title']))
                            <li class="active">{{ $metas['title'] }}</li>
                        @endif
                    @show{{-- 路径导航 --}}
                </ol>
                @if(session('alert'))
                    {!! session('alert') !!}
                @endif
                @section('content')@show{{-- 内容 --}}
            </div>
        </div>
    </div>
@stop