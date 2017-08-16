@extends('layouts.base')
@section('meta')
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
@stop
@section('css')
    <link href="{{ asset('css/bootstrap.css') }}" rel="stylesheet">{{-- BOOTSTRAP CSS --}}
    <link href="{{ asset('css/login.css') }}" rel="stylesheet">{{-- OUR CSS --}}

@stop
@section('js')
    {{--<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>--}}{{-- JQuery --}}
    <script src="{{ asset('js/jquery.min.js') }}"></script>{{-- JQuery JS --}}
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>{{-- BOOTSTRAP JS --}}

    <script src="{{ asset('js/jquery.cxcalendar.min.js') }}"></script>
@stop
@section('body')
    <div class="site-wrapper">
        <div class="site-wrapper-inner">
            <div class="cover-container">
                <div class="inner cover">
                    <h1 class="cover-heading">Crm2.0.beta</h1>

                    <form method="POST" action="/auth/login">
                        {!! csrf_field() !!}
                        <div class="row form-group">
                            <div class="col-lg-12">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="glyphicon glyphicon-user"></i>
                                    </span>
                                    <input type="text" class="form-control" placeholder="Username" name="email" value="{{ old('email') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-lg-12">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="glyphicon glyphicon-eye-close"></i>
                                    </span>
                                    <input type="password" class="form-control" placeholder="Password" name="password">
                                </div>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-lg-12">
                                <div>
                                    <button type="submit" class="btn btn-danger btn-lg">Login Now</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="mastfoot">
                    <div class="inner">
                        <p><a href="javascript:">南京快悦客户管理系统</a> by <a href="javascript:">@霸王小龙龙</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop