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
        // 商品信息 [{"product_id":"2659021","num":1},{"product_id":"100007218425","num":1}]
        $product_infos = $request->input('product_infos');
        $address = $request->get('address');

        $product_infos = json_decode($product_infos);
        $user_id = Auth::user() ?? 1; // 用户id
        $created_at = time();
        $no = uniqid();

        foreach ($product_infos as $info) {
            try {
                $ret = $this->order->create([
                    'user_id' => $user_id,
                    'address' => $address,
                    'pay_at' => $created_at,
                    'no' => $no,
                    'created_at' => $created_at
                ]);
                $product = DB::table('products')->find($info->product_id);
                $data = [
                    'order_id' => $ret->id,
                    'product_id' => $info->product_id,
                    'name' => $product->name,
                    'cover_image' => $product->cover_image,
                    'description' => $product->description,
                    'category_id' => $product->category_id,
                    'on_sale' => $product->on_sale,
                    'original_sale' => $product->original_sale,
                    'distribution' => $product->distribution,
                    // 'created_at' => $created_at
                ];

                DB::table('order_products')->insert($data);

            } catch (\Exception $e) {
                return response()->json(['code' => 500, 'message' => $e->getMessage()]);
            }
        }

        return response()->json(['code' => 200, 'message' => '加入成功']);
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


    //
}
