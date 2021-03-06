<?php


namespace App\Http\Controllers;

use EasyWeChat\Factory;
use EasyWeChat\Kernel\Messages\Image;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


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
            if ($message['MsgType'] == 'event') {
                if ($message['EventKey'] == 'V1001_COMPANY_INTRO') {
                    return new Image('Uv0IQ2jfUJfA8lVaVrGncpnV_AnpsL9jOScjrwvLTxQ');

                    // return "山东莺锦生物科技有限公司成立于2020年1月，是一家集生物技术开发、新材料技术开发、化妆品技术开发、食品饮品销售和进出口经营等业务的综合性公司。公司旗下品牌“卡诺滋”“膜磁场”“莺锦”“博美赫”营销网络已遍布全国，其拥有掌握高精尖技术的专业科研团队及具有二十多年美业丰富经验的专业销售团队，并建立了一套科学规范的管理机制，致力于打造最适合华人女性的创业平台。"; 
                }
                
                if ($message['EventKey'] == 'V1001_CONTACT_US') {
                   return "4000913031"; 
                }
            }
            return "欢迎来到膜磁场^.^";
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
                "type"=> "click",
                "name"=>"公司简介",
                "key"=>"V1001_COMPANY_INTRO"
            ],
            [
                "type" => "view",
                "name" => "产品商城",
                "url" => "http://www.dist.suibian.ink"
            ],
            [
                "type"=> "click",
                "name"=>"联系我们",
                "key"=>"V1001_CONTACT_US"
            ]
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
        Log::info('----' . json_encode($user) . '----');
        // $wechat_user = $user->wechat_user;

        $openid = $user->id;
        $name = $user->name;
        // $user = $user->original;
        // Log::info('*****' . $user['original'] . '*****');
        $wechat_user = $user->original;
        // Log::info('*****' . implode(",", $wechat_user) . '*****');

        // 保存用户信息
        $api_token = uniqid();
        $created_at = time();
        try {
            DB::table('users')->insert([
                'nickname' => $wechat_user['nickname'],
                'realname' => $name,
                'openid' => $openid,
                'sex' => $wechat_user['sex'],
                'city' => $wechat_user['city'],
                'province' => $wechat_user['province'],
                'country' => $wechat_user['country'],
                'headimgurl' => $wechat_user['headimgurl'],
                'api_token' => $api_token,
                'created_at' => $created_at,
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            DB::table('users')->where('openid', $openid)->update([
                'nickname' => $wechat_user['nickname'],
                'realname' => $name,
                'sex' => $wechat_user['sex'],
                'city' => $wechat_user['city'],
                'province' => $wechat_user['province'],
                'country' => $wechat_user['country'],
                'headimgurl' => $wechat_user['headimgurl'],
                'api_token' => $api_token,
                'created_at' => $created_at,
            ]);
        }

        // $_SESSION['wechat_user'] = $user->toArray();
        // $targetUrl = empty($_SESSION['target_url']) ? '/' : $_SESSION['target_url'];

        // header('Location:' . 'http://www.dist.suibian.ink?token=' . $api_token);
        // http://www.dist.suibian.ink/#/?token=' . $api_token
        Log:info('************************************' .  'http://www.dist.suibian.ink/#/?token=' . $api_token);
        header('Location:' . 'http://www.dist.suibian.ink/#/?token=' . $api_token);

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

    public function payCallback()
    {
        // $app = app('wechat.official_account');
        
        $config = [
            'app_id' => env('WECHAT_OFFICIAL_ACCOUNT_APPID', 'your-app-id'),         // AppID
            'secret' => env('WECHAT_OFFICIAL_ACCOUNT_SECRET', 'your-app-secret'),    // AppSecret
            'token' => env('WECHAT_OFFICIAL_ACCOUNT_TOKEN', 'your-token'),           // Token
            'aes_key' => env('WECHAT_OFFICIAL_ACCOUNT_AES_KEY', ''),
            'mch_id' => env('YOUR_MCH_ID', 'YOUR-MCH-ID'),
            'key' => env('KEY_FOR_SIGNATURE', 'KEY-FOR-SIGNATURE'),   // API 密钥
        ];
        $app = Factory::payment($config);

        return $app->handlePaidNotify(function ($message, $fail) {
            Log::info("微信支付回调返回信息 ========> " . json_encode($message));
            $order = $this->checkOrder($message['out_trade_no']);
            if (!$order) {
                Log::info("订单不存在"); // 如果订单不存在
                return true;
            }
            if ($order->status == 1) { //订单已经支付过了
                Log::info("已经支付完成");
                $this->updateOrder($order->id);
                return true; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
            }

            if ($message['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
                // 用户是否支付成功
                Log::info('微信回调通信成功');
                if ($message['result_code'] === 'SUCCESS') {
                    DB::table('pays')->where('id', $order->id)->update([
                       'paid_at' => time(),
                       'status' => 1,
                    ]);
                    // $order->paid_at = time(); // 更新支付时间为当前时间
                    // $order->status = 1;
                    $this->updateOrder($order->order_id);
                    Log::info('更新支付成功');
                    // 用户支付失败
                } elseif ($message['result_code'] === 'FAIL') {
                    DB::table('pays')->where('id', $order->id)->update([
                       'paid_at' => time(),
                       'status' => 2,
                    ]);
                    Log::error("更新支付失败");
                    // $order->status = 2;
                }

                return true;
            } else {
                return $fail('通信失败，请稍后再通知我');
            }
            // 或者错误消息
            // $fail('Order not exists.');
        });
        // $response->send(); // Laravel 里请使用：return $response;
    }

    public function checkOrder($out_trade_no)
    {
        return DB::table('pays')->where('out_trade_no', $out_trade_no)->first();
    }

    /**
     * @param $order_id
     * @return int
     * 更新订单表状态
     */
    public function updateOrder($order_id)
    {
        return DB::table('orders')->where('id', $order_id)->update([
            'pay_status' => 'pending',
            'pay_at' => time()
        ]);

    }
}
