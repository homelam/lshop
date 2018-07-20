<?php

return [
    // 支付宝支付配置
    'alipay' => [
        'app_id'         => env('ALIPAY_APP_ID', ''),
        'ali_public_key' => env('ALIPAY_PUBLIC_KEY', ''),
        'private_key'    => env('ALIPAY_PRIVATE_KEY', ''),
        'log'            => [
            'file' => storage_path('logs/alipay.log'),
        ],
    ],
    // 微信支付配置
    'wechat' => [
        'app_id'      => env('WECHAT_APP_ID', ''), // 工作号 app id
        'mch_id'      => env('MERCHANT_ID', ''), // 第一步获取到的商户号
        'key'         => env('WECHAT_PRIVATE_KEY', ''), // 刚刚设置的 API 密钥
        'cert_client' => '', // 证书路径
        'cert_key'    => '',
        'log'         => [
            'file' => storage_path('logs/wechat_pay.log'),
        ],
    ],
];