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
        $insert_id = DB::table('pays')->insertGetId([
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

            'notify_url' => env('PAY_CALLBACK_URL', '默认的订单回调地址'),     // 你也可以在下单时单独设置来想覆盖它
        ];


        try {
            $out_trade_no = date('Ymd') . $insert_id;
            $app = Factory::payment($config);
            $result = $app->order->unify([
                'body' => '膜磁场订单支付中心',
                'out_trade_no' => $out_trade_no,
                'total_fee' => $total_fee,
                // 'spbill_create_ip' => '123.12.12.123', // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
                // 'notify_url' => 'https://pay.weixin.qq.com/wxpay/pay.action', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
                'trade_type' => 'JSAPI', // 请对应换成你的支付方式对应的值类型
                'openid' => Auth::user()->openid,
            ]);


            Log::info("统一下单接口数据返回 ====== " . json_encode($result));

            $result = json_decode(json_encode($result));

            if ($result->return_code == 'FAIL') {
                return response()->json(['code' => 500, 'message' => '微信下单失败']);
            }


            DB::table('pays')->where('id', $insert_id)->update([
                'appid' => $result->appid,
                'mch_id' => $result->mch_id,
                'nonce_str' => $result->nonce_str,
                'sign' => $result->sign,
                'prepay_id' => $result->prepay_id,
                'trade_type' => $result->trade_type,
                'out_trade_no' => $out_trade_no
            ]);
            return response()->json(['data' => $result, 'code' => 200, 'message' => '微信下单成功']);
        } catch (\Exception $e) {
            Log::error("统一下单接口错误 ------ " . $e->getMessage());
            return response()->json(['code' => 500, 'message' => '微信下单失败']);
        }

    }

    public function orderQuery(Request $request)
    {
        $order_id = $request->get('order_id');
        $pay_order = DB::table('pays')->where('order_id', $order_id)->first();
        if (!$pay_order) {
            return response()->json(['code' => 500, 'message' => '微信订单未找到']);
        }

        if ($pay_order->status == 1) {
            return response()->json(['code' => 200, 'message' => '支付成功']);
        }

        if ($pay_order->status == 2) {
            return response()->json(['code' => 500, 'message' => '支付失败']);
        }

        ///////////// <- 建议在这里调用微信的【订单查询】接口查一下该笔订单的情况，确认是已经支付 /////////////
        $config = [
            'app_id' => env('WECHAT_OFFICIAL_ACCOUNT_APPID', 'your-app-id'),         // AppID
            'secret' => env('WECHAT_OFFICIAL_ACCOUNT_SECRET', 'your-app-secret'),    // AppSecret
            'token' => env('WECHAT_OFFICIAL_ACCOUNT_TOKEN', 'your-token'),           // Token
            'aes_key' => env('WECHAT_OFFICIAL_ACCOUNT_AES_KEY', ''),
            'mch_id' => env('YOUR_MCH_ID', 'YOUR-MCH-ID'),
            'key' => env('KEY_FOR_SIGNATURE', 'KEY-FOR-SIGNATURE'),   // API 密钥
        ];
        $app = Factory::payment($config);

        $result = $app->order->queryByOutTradeNumber($pay_order->out_trade_no);
        Log::info(json_encode($result));
        if ($result['return_code'] === 'SUCCESS' && $result['result_code'] === 'SUCCESS'
            && $result['trade_state'] == 'SUCCESS') {
            $pay_order->paid_at = time(); // 更新支付时间为当前时间
            $pay_order->status = 1;
            return response()->json(['code' => 200, 'message' => '支付成功']);
        }

        // 用户支付失败
        $pay_order->status = 2;
        return response()->json(['code' => 500, 'message' => '支付失败 ===>' . $result['trade_state']]);
    }

}
