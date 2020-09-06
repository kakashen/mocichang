<?php

namespace App\Http\Controllers;

use App\Model\Category;
use App\Model\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    private $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function list(Request $request)
    {
        // $category_id = $request->get('category_id'); // 分类id
        $data = $this->product->where('active', 1)->get();
        return response()->json(['data' => $data, 'code' => 200, 'message' => 'ok']);
    }

    public function add(Request $request)
    {
        $name = $request->input('name'); // 产品名称
        $cover_image = $request->input('cover_image'); // 覆盖图
        $description = $request->get('description'); // 产品描述详情
        $category_id = $request->input('category_id'); // 分类id
        $on_sale = $request->get('on_sale'); // 在售价格 分
        $original_sale = $request->input('original_sale'); // 原价 分
        $in_stock = $request->get('in_stock'); // 库存
        $active = $request->input('active', 1); // 上架=1 下架=0
        $distribution = $request->get('distribution'); // 佣金 分

        $created_at = time();

        $data = $this->product->insert([
            'name' => $name,
            'cover_image' => $cover_image,
            'description' => $description,
            'category_id' => $category_id,
            'on_sale' => $on_sale,
            'original_sale' => $original_sale,
            'in_stock' => $in_stock,
            'active' => $active,
            'distribution' => $distribution,
            'created_at' => $created_at
        ]);
        return response()->json(['code' => 200, 'message' => '插入成功']);
    }

    public function update_stock(Request $request)
    {
        $product_id = $request->get('product_id'); //
        $in_stock = $request->get('in_stock'); //
        try {
            $this->product->where('id', $product_id)->update(['in_stock' => $in_stock]);
            return response()->json(['code' => 200, 'message' => '更新成功']);
        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'message' => '更新失败']);
        }
    }

    public function update(Request $request)
    {
        $name = $request->get('name');
        $cover_image = $request->get('cover_image');
        $description = $request->get('description');
        $category_id = $request->get('category_id');
        $product_id = $request->get('product_id');

        try {
            $this->product->where('id', $product_id)
                ->update([
                    'name' => $name,
                    'cover_image' => $cover_image,
                    'description' => $description,
                    'category_id' => $category_id
                ]);
            return response()->json(['code' => 200, 'message' => '更新成功']);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['code' => 500, 'message' => '更新失败']);

        }
    }

    public function modifyPrice(Request $request)
    {
        $product_id = $request->get('product_id');
        $on_sale = $request->get('on_sale');
        $original_sale = $request->get('original_sale');
        $distribution = $request->get('distribution');

        try {
            $this->product->where('id', $product_id)
                ->update([
                    'on_sale' => $on_sale,
                    'original_sale' => $original_sale,
                    'distribution' => $distribution
                ]);
            return response()->json(['code' => 200, 'message' => '更新成功']);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['code' => 500, 'message' => '更新失败']);

        }
    }


}
