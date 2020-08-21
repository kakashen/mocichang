<?php

namespace App\Http\Controllers;

use App\Model\Category;
use App\Model\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BannerController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct()
    {

    }

    public function list(Request $request)
    {
        $data = DB::table('banners')->orderBy('rank')->get();
        return response()->json(['data' => $data, 'code' => 200, 'message' => 'ok']);
    }

    public function add(Request $request)
    {
        $image = $request->input('image'); // banner图地址
        $product_id = $request->input('product_id'); // banner图关联的产品id
        $rank = $request->get('rank'); // 排序

        try {
            DB::table('banners')->insert([
                'image' => $image,
                'product_id' => $product_id,
                'rank' => $rank,
            ]);
            return response()->json(['code' => 200, 'message' => '插入成功']);

        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'message' => '插入失败']);
        }

    }

    public function update(Request $request)
    {
        $id = $request->get('id'); // banner  id
        $image = $request->input('image'); // banner图地址
        $product_id = $request->input('product_id'); // banner图关联的产品id
        $rank = $request->get('rank'); // 排序

        try {
            DB::table('banners')->where('id', $id)
                ->update([
                    'image' => $image,
                    'product_id' => $product_id,
                    'rank' => $rank,
                ]);
            return response()->json(['code' => 200, 'message' => '修改成功']);

        } catch (\Exception $e) {
//            return response()->json(['code' => 200, 'message' => '修改失败']);
            return response()->json(['code' => 200, 'message' => $e->getMessage()]);
        }
    }

}
