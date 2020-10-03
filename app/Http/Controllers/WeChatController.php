<?php


namespace App\Http\Controllers;

use EasyWeChat\Factory;
use Illuminate\Http\Request;

use Log;

class WeChatController extends Controller
{
    /**
     * 处理微信的请求消息
     *
     * @return string
     */
    public function serve()
    {
        Log::info('request arrived.');
        # 注意：Log 为 Laravel 组件，所以它记的日志去 Laravel 日志看，而不是 EasyWeChat 日志

        $app = app('wechat.official_account');
        $app->server->push(function($message){
            return "你好坏呀！";
        });

        return $app->server->serve();
    }

    /**
     * @return mixed
     * 读取（查询）已设置菜单
     */
    public function list()
    {
        $app = app('wechat.official_account');
        return $app->menu->list();
    }


    /**
     * @return mixed
     * 获取当前菜单
     */
    public function current()
    {
        $app = app('wechat.official_account');
        return $app->menu->current();
    }

    /**
     * 添加普通菜单
     */
    public function create()
    {
        $buttons = [
            [
                "type" => "click",
                "name" => "今日歌曲",
                "key"  => "V1001_TODAY_MUSIC"
            ],
            [
                "name"       => "菜单",
                "sub_button" => [
                    [
                        "type" => "view",
                        "name" => "膜磁场",
                        "url"  => "http://www.api.suibian.ink/wechat/oauth"
                    ],
                    [
                        "type" => "view",
                        "name" => "视频",
                        "url"  => "http://v.qq.com/"
                    ],
                    [
                        "type" => "click",
                        "name" => "赞一下",
                        "key" => "V1001_GOOD"
                    ],
                ],
            ],
        ];
        $app = app('wechat.official_account');
        $app->menu->create($buttons);
    }

    public function callback(Request $request)
    {
        Log::info($request);
        $app = app('wechat.official_account');
        $oauth = $app->oauth;

        // 获取 OAuth 授权结果用户信息
        $code = "微信回调URL携带的 code";
        $user = $oauth->userFromCode();

        $_SESSION['wechat_user'] = $user->toArray();

        $targetUrl = empty($_SESSION['target_url']) ? '/' : $_SESSION['target_url'];

        header('Location:'. 'http://www.dist.suibian.ink');

    }

    public function oauth()
    {
        $config = [
            // ...
            'oauth' => [
                'scopes'   => ['snsapi_userinfo'],
                'callback' => '/oauth_callback',
            ],
            // ..
        ];

        $app = Factory::officialAccount($config);
        $oauth = $app->oauth;
        // 未登录
        if (empty($_SESSION['wechat_user'])) {

            $_SESSION['target_url'] = 'http://www.dist.suibian.ink';

            return $oauth->redirect();
            // 这里不一定是return，如果你的框架action不是返回内容的话你就得使用
            // $oauth->redirect()->send();
        }
        // 已经登录过
        $user = $_SESSION['wechat_user'];
        header('Location:'. $_SESSION['target_url']);

    }


}
