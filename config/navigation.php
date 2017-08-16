<?php

/**
 * 导航配置文件
 *
 * name: 主导航名称
 * icon: 导航图标
 * url: 导航链接
 * subnavigations: 子菜单集合
 *      name: 子菜单名称
 *      url: 子菜单链接
 *      icon: 子菜单图标
 *
 * @author Vincent <nyewon@gmail.com>
 */
return [
    [
        'name' => '信息',
        'icon' => 'envelope',
        'url' => 'message.index',
    ],
    [
        'name' => '发送队列',
        'icon' => 'send',
        'url' => 'messageReply.index',
    ],
    [
        'name' => '信息模版',
        'url' => '',
        'icon' => 'list-alt',
        'subnavigations' => [
            [
                'name' => '模版类型',
                'url' => 'messageTemplateType.index',
                'icon' => '',
            ],
            [
                'name' => '信息模版',
                'url' => 'messageTemplate.index',
                'icon' => '',
            ],
        ],
    ],
    [
        'name' => '直接发邮件',
        'icon' => 'send',
        'url' => 'sendemail.index',
    ],
    [
        'name' => '报表',
        'icon' => 'excel',
        'url' => '',
        'subnavigations' => [
            [
                'name' => '按客服统计',
                'icon' => 'excel',
                'url' => 'Excel.index',
            ],
        ],
    ],
    [
        'name' => '投诉类型',
        'url' => '',
        'icon' => '',
        'subnavigations' => [
            [
                'name' => '投诉类型_(sku)',
                'icon' => 'excel',
                'url' => 'messageorder.index',
            ],
            [
                'name' => '投诉类型_(解决方案)',
                'icon' => 'excel',
                'url' => 'complaint.index',
            ],
        ],
    ],
    [
        'name' => '转发转交',
        'url' => '',
        'icon' => '',
        'subnavigations' => [
            [
                'name' => '转发邮件',
                'icon' => 'send',
                'url' => 'forwardemail.index',
            ],
            [
                'name' => '转交历史',
                'icon' => 'send',
                'url' => 'message_log.index',
            ],
            [
                'name' => '系统抄送',
                'icon' => 'floppy-disk',
                'url' => 'message.systemList',
            ],
        ],
    ],
    [
        'name' => '系统',
        'url' => '',
        'icon' => 'cog',
        'subnavigations' => [
            [
                'name' => '渠道',
                'url' => 'channel.index',
                'icon' => '',
            ],
            [
                'name' => '账号',
                'url' => 'account.index',
                'icon' => '',
            ],
            [
                'name' => '用户',
                'icon' => 'user',
                'url' => 'user.index',
            ],
        ],
    ],
];

