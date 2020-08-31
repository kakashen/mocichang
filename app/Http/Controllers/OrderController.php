<?php

namespace App\Http\Controllers;

use App\Model\Cart;
use App\Model\Category;
use App\Model\Order;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    private $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function list(Request $request)
    {
        $pay_status = $request->get('pay_status'); // 0未支付 1已支付
        $data = $this->order->where('pay_status', $pay_status)
            ->orderBy('created_at')->with('product')->get();
        return response()->json(['data' => $data, 'code' => 200, 'message' => 'ok']);
    }

    public function add(Request $request)
    {
        $total_price = $request->input('total_price');
        $address = $request->get('address');


        $user_id = Auth::user() ?? 1; // 用户id
        $created_at = time();
        $no = uniqid();

        // 购物车
        $cart_infos = DB::table('carts')->where('user_id', $user_id)->get();

        foreach ($cart_infos as $cart_info) {
            $stock = DB::table('products')->find($cart_info->product_id);
            if ($stock->in_stock >= $cart_info->amount) {
                continue;
            }
            return response()->json(['code' => 500, 'message' => "$stock->name 库存不足"]);
        }
        // 初始化一个订单
        $data = [
            'pay_at' => $created_at,
            'no' => $no,
            'user_id' => $user_id,
            'address' => $address,
            'created_at' => $created_at,
        ];
        $ret = $this->order->create($data);

        $total_amount = 0; // 总价

        foreach ($cart_infos as $info) {
            try {
                $product = DB::table('products')->find($info->product_id);
                $data = [
                    'order_id' => $ret->id,
                    'product_id' => $info->product_id,
                    'name' => $product->name,
                    'amount' => $info->amount,
                    'cover_image' => $product->cover_image,
                    'description' => $product->description,
                    'category_id' => $product->category_id,
                    'on_sale' => $product->on_sale,
                    'original_sale' => $product->original_sale,
                    'distribution' => $product->distribution,
                    // 'created_at' => $created_at
                ];

                DB::table('products')->where('id', $info->product_id)
                    ->decrement('in_stock', $info->amount);

                DB::table('order_products')->insert($data);

                $total_amount += $product->on_sale * $info->amount;
                $this->order->where('id', $ret->id)->update(['total_price' => $total_amount]);

                if (Auth::user()->head_openid ?? 'fff') {
                    DB::table('users')
                        ->where('id', Auth::user()->id ?? 1)
                        ->increment('account', $product->distribution);
                }
            } catch (\Exception $e) {
                return response()->json(['code' => 500, 'message' => $e->getMessage()]);
            }
        }

        return response()->json(['code' => 200, 'message' => '下单成功']);
    }

    public function delete(Request $request)
    {
        $cart_id = $request->get('cart_id');
        try {
            $this->order->where('id', $cart_id)
                ->where('user_id', Auth::user()->id ?? 1)->delete();
            return response()->json(['code' => 200, 'message' => '删除成功']);

        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'message' => '删除失败']);
        }
    }

    public function show(Request $request)
    {
        $page = intval($request->get('page')); // 第几页
        $per_page = intval($request->get('per_page')); // 每页多少
        $pay_status = $request->get('pay_status'); //

        try {
            $query = $this->order->where('pay_status', $pay_status);
            $data = $query->skip(($page - 1) * $per_page)
                ->take($per_page)
                ->with('product')
                ->get();
            $total = $query->count();
            return response()->json(['data' => $data, 'total' => $total, 'code' => 200, 'message' => '获取成功']);

        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'message' => '获取失败']);

        }
    }


    //
}
