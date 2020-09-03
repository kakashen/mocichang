<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;

class ImageController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct()
    {
    }

    public function upload(Request $request)
    {
        $path = $request->file('image')->store('images');
        return response()->json(['data' => $path, 'code' => 200, 'message' => '上传成功']);
    }

}
