@extends('layouts.default')
@section('content')
    <!--dashboard-->
    <!-- MetisMenu CSS -->
    <link href="{{ asset('plugins/dashboard/metisMenu.min.css') }}" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('plugins/dashboard/sb-admin-2.css') }}" rel="stylesheet">
    <!-- Morris Charts CSS -->
    <link href="{{ asset('plugins/dashboard/morris.css') }}" rel="stylesheet">
    <!-- Custom Fonts -->
    <link href="{{ asset('plugins/dashboard/font-awesome.min.css') }}" rel="stylesheet" type="text/css">

    <div id="wrapper">

        <div>
            <div class="row" style="display: none;">
                <div class="col-lg-12">
                    <h1 class="page-header">Dashboard</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="panel panel-yellow">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-envelope fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">{{$me_unread_count}}</div>
                                    <div></div>
                                </div>
                            </div>
                        </div>
                        <a target="_blank" href="message">
                            <div class="panel-footer">
                                <span class="pull-left">我的未读邮件数</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-bell fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">{{$me_process_count}}</div>
                                    <div></div>
                                </div>
                            </div>
                        </div>
                        <a target="_blank" href="message">
                            <div class="panel-footer">
                                <span class="pull-left">我的待处理邮件数</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="panel panel-green">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-tasks fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">{{$me_complete_count}}</div>
                                    <div></div>
                                </div>
                            </div>
                        </div>
                        <a target="_blank" href="message">
                            <div class="panel-footer">
                                <span class="pull-left">我的已回复邮件数</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="panel panel-danger">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-warning fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">{{$reply_fail_count}}</div>
                                    <div></div>
                                </div>
                            </div>
                        </div>
                        <a target="_blank" href="messageReply">
                            <div class="panel-footer">
                                <span class="pull-left">未发送及发送失败监控</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-twitter fa-fw"></i><strong> 我负责的渠道账号 </strong>
                    </div>
                    <div class="panel-body">
                        <div class="">
                            <div class="row">
                                <div class="col-lg-12">
                                    <table class="table table-bordered table-striped table-hover sortable">
                                        <thead>
                                        <tr>
                                            <th style="width: 50px;">序号</th>
                                            <th class="col-lg-3">渠道</th>
                                            <th>渠道账号</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($user_accounts as $key => $v)
                                            <tr>
                                                <td>{{$key+1}}</td>
                                                <td>{{$v->account_name ? $v->account_name->channel->name: ''}}</td>
                                                <td>{{$v->account_name ? $v->account_name->account: ''}}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

@stop