<?php


namespace App\Http\Controllers;

use EasyWeChat\Factory;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
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
        $app->server->push(function ($message) {
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
                "type" => "view",
                "name" => "膜磁场",
                "url" => "http://www.dist.suibian.ink"
            ],
        ];
        $app = app('wechat.official_account');
        $app->menu->create($buttons);
    }

    public function callback(Request $request)
    {
        Log::info($request);
        $app = app('wechat.official_account');
        $user = $app->oauth->user();

        // 获取 OAuth 授权结果用户信息
        // $code = "微信回调URL携带的 code";
        // $user = $oauth->userFromCode($request['code']);
        Log:
        info('----' . $user . '----');
        $wechat_user = $user->wechat_user;
        $openid = $wechat_user->id;

        // 保存用户信息
        $api_token = uniqid();
        $created_at = time();
        try {
            DB::table('users')->insert([
                'nickname' => $wechat_user->nickname,
                'realname' => $wechat_user->name,
                'openid' => $wechat_user->openid,
                'sex' => $wechat_user->sex,
                'city' => $wechat_user->city,
                'province' => $wechat_user->province,
                'country' => $wechat_user->country,
                'headimgurl' => $wechat_user->headimgurl,
                'api_token' => $api_token,
                'created_at' => $created_at,
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            DB::table('users')->where('openid', $openid)->update([
                'nickname' => $wechat_user->nickname,
                'realname' => $wechat_user->name,
                'sex' => $wechat_user->sex,
                'city' => $wechat_user->city,
                'province' => $wechat_user->province,
                'country' => $wechat_user->country,
                'headimgurl' => $wechat_user->headimgurl,
                'api_token' => $api_token,
                'created_at' => $created_at,
            ]);
        }

        // $_SESSION['wechat_user'] = $user->toArray();
        // $targetUrl = empty($_SESSION['target_url']) ? '/' : $_SESSION['target_url'];

        header('Location:' . 'http://www.dist.suibian.ink?token=' . $api_token);

    }

    public function oauth()
    {
        $config = [
            // ...
            'app_id' => env('WECHAT_OFFICIAL_ACCOUNT_APPID', 'your-app-id'),         // AppID
            'secret' => env('WECHAT_OFFICIAL_ACCOUNT_SECRET', 'your-app-secret'),    // AppSecret
            'token' => env('WECHAT_OFFICIAL_ACCOUNT_TOKEN', 'your-token'),           // Token
            'aes_key' => env('WECHAT_OFFICIAL_ACCOUNT_AES_KEY', ''),
            'oauth' => [
                'scopes' => ['snsapi_userinfo'], // snsapi_userinfo snsapi_base
                'callback' => 'http://www.api.suibian.ink/callback',
            ],
            // ..
        ];

        $app = Factory::officialAccount($config);
        $oauth = $app->oauth;
        return $oauth->redirect();

        // 未登录
        /*if (empty($_SESSION['wechat_user'])) {

            $_SESSION['target_url'] = 'http://www.dist.suibian.ink';

            return $oauth->redirect();
            // 这里不一定是return，如果你的框架action不是返回内容的话你就得使用
            // $oauth->redirect()->send();
        }*/
        // 已经登录过
        // $user = $_SESSION['wechat_user'];
        // header('Location:'. $_SESSION['target_url']);

    }


}
