<?php

// 第三方登录配置文件
return [
    // 微信 扫描登录  ?? 是否不能扫描登录 需要自己集成？
    'wechat' => [
        'client_id'     => env('WECHAT_OFFICIAL_ACCOUNT_APPID', ''),
        'client_secret' => env('WECHAT_OFFICIAL_ACCOUNT_SECRET', ''),
        'redirect'      => env('APP_URL') . '/oauth/wechat/callback',
    ],
    // google 谷歌登录
    'google' => [
        'client_id'     => env('GOOGLE_OFFICIAL_ACCOUNT_APPID', ''),
        'client_secret' => env('GOOGOLE_OFFICIAL_ACCOUNT_SECRET', ''),
        'redirect'      => env('APP_URL') . '/oauth/google/callback',
    ],

    // facebook  需要强制使用 https
    'facebook' => [
        'client_id'     => env('FACEBOOK_OFFICIAL_ACCOUNT_APPID', ''),
        'client_secret' => env('FACEBOOK_OFFICIAL_ACCOUNT_SECRET', ''),
        'redirect'      => env('APP_URL') . '/oauth/facebook/callback',
    ],

    // QQ qq登录
    'qq' => [
        'client_id'     => env('QQ_OFFICIAL_ACCOUNT_APPID', ''),
        'client_secret' => env('QQ_OFFICIAL_ACCOUNT_SECRET', ''),
        'redirect'      => env('APP_URL') . '/oauth/qq/callback',
    ],
    
    // 企业微信登录
    'wework' => [
        'client_id'     => env('WEWORK_OFFICIAL_ACCOUNT_APPID', ''),
        'client_secret' => env('WEWORK_OFFICIAL_ACCOUNT_SECRET', ''),
        'redirect'      => env('APP_URL') . '/oauth/wework/callback',
    ],
];