<?php

namespace App\Http\Controllers;

use App\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    private $admin;
    public function __construct(Admin $admin)
    {
        $this->admin = $admin;
    }

    public function login(Request $request)
    {
        $name = $request->get('name');
        $password = $request->get('password');
        $query = $this->admin->where('name', $name)->where('password', $password);
        if (!$query->first()) {
            return response()->json(['data' => [], 'code' => 500, 'message' => '账号或密码错误']);
        }
        try {
            $token = uniqid();
            $query->update(['token' => $token]);
            return response()->json(['data' => [
                'name' => $name,
                'token' => $token,
            ], 'code' => 200, 'message' => '登录成功']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['data' => [
            ], 'code' => 500, 'message' => '登录失败, 请稍后再试!']);
        }

    }

}
