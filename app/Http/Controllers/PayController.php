<?php

namespace App\Http\Controllers;

use App\Model\Pay;
use Illuminate\Http\Request;
use EasyWeChat\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    private $pay;

    public function __construct(Pay $pay)
    {
        $this->pay = $pay;
    }

    public function unify(Request $request)
    {
        $order_id = $request->get('order_id'); // 订单id
        $total_fee = $request->get('total_fee', 88); // 总金额
        DB::table('pays')->insert([
            'order_id' => $order_id,
            'total_fee' => $total_fee,
            'openid' => Auth::user()->openid,
            'created_at' => time()
        ]);

        $config = [
            // ...
            'app_id' => env('WECHAT_OFFICIAL_ACCOUNT_APPID', 'your-app-id'),         // AppID
            'secret' => env('WECHAT_OFFICIAL_ACCOUNT_SECRET', 'your-app-secret'),    // AppSecret
            'token' => env('WECHAT_OFFICIAL_ACCOUNT_TOKEN', 'your-token'),           // Token
            'aes_key' => env('WECHAT_OFFICIAL_ACCOUNT_AES_KEY', ''),

            // 'app_id'             => 'xxxx',
            'mch_id' => env('YOUR_MCH_ID', 'YOUR-MCH-ID'),
            'key' => env('KEY_FOR_SIGNATURE', 'KEY-FOR-SIGNATURE'),   // API 密钥
            // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
            // 'cert_path' => env('', 'PATH/TO/YOUR/CERT.PEM'), // XXX: 绝对路径！！！！
            // 'key_path' => env('', 'PATH/TO/YOUR/KEY'),      // XXX: 绝对路径！！！！

            'notify_url' => env('', '默认的订单回调地址'),     // 你也可以在下单时单独设置来想覆盖它
        ];


        try {
            $app = Factory::payment($config);
            $result = $app->order->unify([
                'body' => '膜磁场订单支付中心',
                'out_trade_no' => microtime(true) * 10000,
                'total_fee' => $total_fee,
                // 'spbill_create_ip' => '123.12.12.123', // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
                // 'notify_url' => 'https://pay.weixin.qq.com/wxpay/pay.action', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
                'trade_type' => 'JSAPI', // 请对应换成你的支付方式对应的值类型
                'openid' => Auth::user()->openid,
            ]);
            Log::info("统一下单接口数据返回 ====== " . json_encode($result));
            return response()->json(['data' => $result, 'code' => 200, 'message' => '微信下单成功']);
        } catch (\Exception $e) {
            Log::error("统一下单接口错误 ------ " . $e->getMessage());
            return response()->json(['code' => 500, 'message' => '微信下单失败']);
        }

    }

}
