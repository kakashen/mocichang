<?php

namespace App\Http\Controllers;

use App\Model\Cart;
use App\Model\Category;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    private $cart;
    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    public function list()
    {
        $data = $this->cart->with('product')->get();
        return response()->json(['data' => $data, 'code' => 200, 'message' => 'ok']);
    }

    public function add(Request $request)
    {
        $product_id = $request->input('product_id'); // 商品id
        $amount = $request->get('amount', 1); // 分类描述
        $user_id = Auth::user() ?? 1; // 用户id
        $created_at = time();
        try {

            $query = $this->cart->where('product_id', $product_id)->where('user_id', $user_id);
            if ($query->first()) {
                try {
                    $query->increment('amount');
                    return response()->json(['code' => 200, 'message' => '加入成功']);
                } catch (\Exception $e) {
                    return response()->json(['code' => 500, 'message' => '加入失败']);
                }
            }

            $this->cart->insert([
                'product_id' => $product_id,
                'amount' => $amount,
                'user_id' => $user_id,
                'created_at' => $created_at
            ]);
            return response()->json(['code' => 200, 'message' => '加入成功']);

        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'message' => '加入失败']);
        }
    }

    public function delete(Request $request)
    {
        $product_id = $request->get('product_id');

        try {
            $query = $this->cart->where('product_id', $product_id)
                ->where('user_id', Auth::user()->id ?? 1);

            $query->decrement('amount');
            if ($query->first()->amount == 0) {
                $query->delete();
            }
            return response()->json(['code' => 200, 'message' => '删除成功']);

        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'message' => '删除失败']);
        }
    }



    //
}
