<?php
/**
 * 渠道配置文件
 *
 * 2016-01-04
 * @author Vincent <nyewon@gmail.com>
 */
return [
    //渠道国家
    'countries' => ['GLOBAL', 'US', 'UK', 'CN', 'DE', 'FR', 'JP'],
    //渠道币种
    'currencies' => ['ALL', 'USD', 'GBP', 'EUR'],
    //API类型
    'api_type' => [
        'amazon' => 'amazon',
        'ebay' => 'ebay',
        'aliexpress' => 'aliexpress',
    ],
    'is_active' => [
        '1' => '有效',
        '0' => '无效',
    ],
];

