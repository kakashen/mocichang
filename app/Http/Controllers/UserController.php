<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;


class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    private $user;
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function show(Request $request)
    {
        $page = intval($request->get('page')); // 第几页
        $per_page = intval($request->get('per_page')); // 每页多少

        try {
            $query = $this->user;
            $data = $query->skip(($page - 1) * $per_page)
                ->take($per_page)
                ->get();
            $total = $query->count();
            return response()->json(['data' => $data, 'total' => $total, 'code' => 200, 'message' => '获取成功']);

        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'message' => '获取失败']);
        }
    }

}
