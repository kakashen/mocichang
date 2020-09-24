<?php
use EasyWeChat\Factory;

$config = [
    'app_id' => env('WECHAT_OFFICIAL_ACCOUNT_APPID'),
    'secret' => env('WECHAT_OFFICIAL_ACCOUNT_APPID'),
    'token' => env('WECHAT_OFFICIAL_ACCOUNT_APPID'),
    'response_type' => 'array',
    //...
];

$app = Factory::officialAccount($config);

$response = $app->server->serve();

// 将响应输出
// $response->send();exit;
return $response;
// Laravel 里请使用：return $response;
