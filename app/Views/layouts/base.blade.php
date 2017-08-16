<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">{{-- favicon --}}
    <title>{{ $metas['title'] or 'Coffee' }} - Coffee</title>
    @section('meta')@show{{-- META申明 --}}
    @section('css')@show{{-- CSS样式表 --}}
    @section('js')@show{{-- JS脚本 --}}
    @section('init')@show{{-- 初始化 --}}
    {{-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries --}}
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
@section('body')@show{{-- 正文部分 --}}
@section('pageJs')@show{{-- 页面JS脚本 --}}
</body>
</html>