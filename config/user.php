<?php

/**
 * 用户配置文件
 *
 * @author Vincent <nyewon@gmail.com>
 */
return [
    'group' => [
        'staff' => '客服',
        'leader' => '主管',
    ],
    'is_login' => [
        '1' => '激活登陆',
        '0' => '不可登陆',
    ],
    'staff'=>array(6,10,15,16,19,20,21),
    //转交邮件
    'email' => [
        'business@choies.com' => 'business@choies.com',
        'service_es@choies.com' => 'service_es@choies.com',
        'service_fr@choies.com' => 'service_fr@choies.com',
        'zoe@choies.com' => 'zoe@choies.com',
        'kathy@choies.com' => 'kathy@choies.com',
    ],
    //管理员账户
    'keader'=>array(6,9,10,15,23,25,28),
];