<?php

namespace App\Http\Controllers;

use App\Model\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    private $category;
    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    public function list()
    {
        $data = $this->category->orderBy('rank')->get();
        return response()->json(['data' => $data, 'code' => 200, 'message' => 'ok']);
    }

    public function add(Request $request)
    {
        $name = $request->input('name'); // 分类名称
        $description = $request->get('description'); // 分类描述
        $rank = $request->get('rank'); // 分类排序
        $created_at = time();
        try {
            $this->category->insert([
                'name' => $name,
                'description' => $description,
                'rank' => $rank,
                'created_at' => $created_at
            ]);
            return response()->json(['code' => 200, 'message' => '插入成功']);

        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'message' => '插入失败']);
        }



    }

    public function update(Request $request)
    {
        $id = $request->get('id');
        $name = $request->input('name');
        $description = $request->get('description');
        $created_at = time();
        try {
            $this->category->where('id', $id)->update([
                'name' => $name,
                'description' => $description,
                'created_at' => $created_at
            ]);
            return response()->json(['code' => 200, 'message' => '修改成功']);

        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'message' => '修改失败']);

        }

    }



    //
}
