<?php
/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register all of the routes for an application.
  | It's a breeze. Simply tell Laravel the URIs it should respond to
  | and give it the controller to call when that URI is requested.
  |
 */

// Authentication routes...
Route::get('auth/login', 'Auth\AuthController@getLogin');
Route::post('auth/login', 'Auth\AuthController@postLogin');
Route::get('auth/logout', 'Auth\AuthController@getLogout');

// Registration routes...
Route::get('auth/register', 'Auth\AuthController@getRegister');
Route::post('auth/register', 'Auth\AuthController@postRegister');

Route::any('/home', 'HomeController@index');
//api   choies 反馈问题传值给crm

Route::any('api_choies/create', 'ApichoiesController@create');
# 获取crm系统客服回复邮件总数
Route::any('api/get_replay_count', 'ApierpController@api_get_replay_count');
# 获取crm系统未读邮件，未处理邮件
Route::any('api/api_get_msg_count', 'ApierpController@api_get_msg_count');
# 获取crm系统客服的投诉和退款信息(前三天)
Route::any('api/api_get_complaint', 'ApierpController@api_get_complaint');

Route::group(['middleware' => 'auth'], function () {
    //首页路由
    Route::any('/', ['as' => 'dashboard.index', 'uses' => 'DashboardController@index']);

    /**
     * 信息路由
     */
    //系统邮件路由
    Route::any('message/systemList',
        ['as' => 'message.systemList', 'uses' => 'MessageController@systemList']);
    //处理信息
    Route::any('message/process',
        ['as' => 'message.process', 'uses' => 'MessageController@process']);

    //处理信息1
    Route::any('message/{id}/process1',
        ['as' => 'message.process1', 'uses' => 'MessageController@process1']);

    //处理信息test
    Route::any('message/processtest',
        ['as' => 'message.processtest', 'uses' => 'MessageController@processtest']);

    //开启工作流
    Route::any('message/startWorkflow',
        ['as' => 'message.startWorkflow', 'uses' => 'MessageController@startWorkflow']);
    //邮件内容
    Route::any('message/{id}/content',
        ['as' => 'message.content', 'uses' => 'MessageController@content']);
    //开启工作流
    Route::any('message/{id}/endWorkflow',
        ['as' => 'message.endWorkflow', 'uses' => 'MessageController@endWorkflow']);
    //设置关联订单
    Route::any('message/{id}/setRelatedOrders',
        ['as' => 'message.setRelatedOrders', 'uses' => 'MessageController@setRelatedOrders']);
    //取消关联订单
    Route::any('message/{id}/cancelRelatedOrder/{relatedOrderId}',
        ['as' => 'message.cancelRelatedOrder', 'uses' => 'MessageController@cancelRelatedOrder']);
    //无需关联订单
    Route::any('message/{id}/notRelatedOrder',
        ['as' => 'message.notRelatedOrder', 'uses' => 'MessageController@notRelatedOrder']);
    //转交他人
    Route::any('message/{id}/assignToOther',
        ['as' => 'message.assignToOther', 'uses' => 'MessageController@assignToOther']);
    Route::resource('message', 'MessageController');
    //回复信息
    Route::any('message/{id}/reply',
        ['as' => 'message.reply', 'uses' => 'MessageController@reply']);
    //无需回复
    Route::any('message/{id}/notRequireReply',
        ['as' => 'message.notRequireReply', 'uses' => 'MessageController@notRequireReply']);
    Route::resource('message', 'MessageController');

    //信息模版类型路由
    Route::any('messageTemplateType/ajaxGetChildren',
        ['as' => 'messageTemplateType.ajaxGetChildren', 'uses' => 'Message\Template\TypeController@ajaxGetChildren']);
    Route::any('messageTemplateType/ajaxGetTemplates',
        ['as' => 'messageTemplateType.ajaxGetTemplates', 'uses' => 'Message\Template\TypeController@ajaxGetTemplates']);
    Route::resource('messageTemplateType', 'Message\Template\TypeController');

    //信息模版路由
    Route::any('messageTemplate/ajaxGetTemplate',
        ['as' => 'messageTemplate.ajaxGetTemplate', 'uses' => 'Message\TemplateController@ajaxGetTemplate']);
    Route::resource('messageTemplate', 'Message\TemplateController');

    //回复队列路由
    Route::resource('messageReply', 'Message\ReplyController');
    // 发送队列再次发送
    Route::any('messageReply/{id}/replysendmsg',
        ['as' => 'messageReply.replysendmsg', 'uses' => 'Message\ReplyController@replysendmsg']);
    //上传文件
    Route::any('message/updatefile',
        ['as' => 'message.updatefile', 'uses' => 'MessageController@updatefile']);
    Route::any('message/{id}/updateimg',
        ['as' => 'message.updateimg', 'uses' => 'MessageController@updateimg']);
    //转发邮件
    Route::any('message/forwardemail',
        ['as' => 'message.forwardemail', 'uses' => 'MessageController@forwardemail']);
    //邮件转发路由
    Route::resource('forwardemail', 'Message\ForemailController');
    Route::resource('showkai', 'MessageController@showkai');
    //邮件转发控制器
    Route::any('message/{id}/foremail',
        ['as' => 'message.foremail', 'uses' => 'MessageController@foremail']);
    Route::get('forwardemail/edit/{id}', 'Message\ForemailController@edit');

    //用户路由
    Route::resource('user', 'UserController');

    //转发邮件
    Route::resource('message_log', 'Message\Messages_logController');

    //直接发邮件
    Route::resource('sendemail', 'Message\SendemailController');
    //新建发邮件页面
    Route::any('sendemail/create', 'Message\SendemailController@create');
    //保存发邮件
    Route::any('sendemail/save',
        ['as' => 'sendemail.save', 'uses' => 'Message\SendemailController@save']);
    //保存发邮件(带附件)
    Route::any('sendemail/saveFile',
        ['as' => 'sendemail.saveFile', 'uses' => 'Message\SendemailController@saveFile']);
    Route::get('sendemail/edit/{id}', 'Message\SendemailController@edit');
    //稍后处理
    Route::any('message/{id}/dontRequireReply',
        ['as' => 'message.dontRequireReply', 'uses' => 'MessageController@dontRequireReply']);

    //新增批量处理无需回复订单
    Route::any('message/notsRequireReply',
        ['as' => 'message.notsRequireReply', 'uses' => 'MessageController@notsRequireReply']);

    //新增单个无需回复
    Route::any('message/{id}/notRequireReply_1',
        ['as' => 'message.notRequireReply_1', 'uses' => 'MessageController@notRequireReply_1']);
    //新增单个无需回复
    Route::any('testGetMessgae','MessageController@testGetMessgae')->name('testGetMessgae');

    //新增批量分配邮件
    Route::any('message/assigned',
        ['as' => 'message.assigned', 'uses' => 'MessageController@assigned']);

    //报表
    Route::resource('Excel', 'ExcelController');
    Route::any('Excel/export',
        ['as' => 'Excel.export', 'uses' => 'ExcelController@export']);

    Route::any('Excel/export_report',
        ['as' => 'Excel.export_report', 'uses' => 'ExcelController@export_report']);

    //投诉类型
    Route::resource('complaint', 'Message\ComplaintController');
    Route::any('complaint/save',
        ['as' => 'complaint.save', 'uses' => 'Message\ComplaintController@save']);
    Route::any('complaint/export',
        ['as' => 'complaint.export', 'uses' => 'Message\ComplaintController@export']);
    Route::get('complaint/edit/{id}', 'Message\ComplaintController@edit');
    //投诉类型2
    Route::resource('messageorder', 'Message\MessageorderController');
    Route::any('messageorder/save',
        ['as' => 'messageorder.save', 'uses' => 'Message\MessageorderController@save']);
    Route::any('messageorder/export',
        ['as' => 'messageorder.export', 'uses' => 'Message\MessageorderController@export']);
    Route::get('messageorder/edit/{id}', 'Message\MessageorderController@edit');


    //投诉类型模板
    Route::any('complaint/ajaxGetChildren1',
        ['as' => 'complaint.ajaxGetChildren1', 'uses' => 'Message\ComplaintController@ajaxGetChildren1']);

    //渠道路由
    Route::resource('channel', 'ChannelController');

    //账号路由
    Route::resource('account', 'AccountController');

    Route::post('channelAccount/updateApi/{id}',
        ['uses' => 'AccountController@updateApi', 'as' => 'channelAccount.updateApi']);
    //百度翻译
    Route::any('ajaxGetTranInfo',
        ['as' => 'ajaxGetTranInfo', 'uses' => 'MessageController@ajaxGetTranInfo']);

    //ebay 纠纷
    Route::resource('ebayCases', 'Message\EbayCasesController');
    Route::any('MessageToBuyer', ['as' => 'MessageToBuyer', 'uses' => 'Message\EbayCasesController@MessageToBuyer']);
    Route::any('AddTrackingDetails',
        ['as' => 'AddTrackingDetails', 'uses' => 'Message\EbayCasesController@AddTrackingDetails']);
    Route::any('RefundBuyer', ['as' => 'case.RefundBuyer', 'uses' => 'Message\EbayCasesController@RefundBuyer']);
    Route::any('PartRefundBuyer',
        ['as' => 'case.PartRefundBuyer', 'uses' => 'Message\EbayCasesController@PartRefundBuyer']);
});