<?php

require './vendor/autoload.php';

use HoseaDevops\TinySms\TinySms;

$config = [
    // HTTP 请求的超时时间（秒）
    'timeout' => 5.0,

    'default'     => [
        'signature'  => '【九斗鱼】',  // 签名 短信签名
        // 默认发送配置
        'default'   => [
            // 网关调用策略，默认：顺序调用
            'strategy' => \HoseaDevops\TinySms\Strategies\OrderStrategy::class,
            // 默认可用的发送网关
            'gateways' => [
                'chuanglan'
            ],
        ],
        // 可用的网关配置
        'gateways' => [
            //创蓝 https://www.253.com/api-docs.html
            'chuanglan' => [
                //营销短信
                'MARKET'=> [
                    'username'            => 'M3653525',
                    'password'            => 'gHLwj2hmZy4b40',
                    'url'                 => 'http://smssh1.253.com',
                    'signature_position'  => 'top',  // 签名位置 top || bottom
                ]
            ],
        ],
    ],
];

$easySms = new TinySms($config, 'default', 'MARKET');

$easySms->send(15201594661, [
        'content'  => '您的验证码为: 6379'
]);
