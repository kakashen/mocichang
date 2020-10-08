<?php

namespace App\Http\Controllers;

use App\Model\Cart;
use App\Model\Category;
use App\Model\Order;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
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

        $data = DB::table('user_addresses')
            ->where('user_id', Auth::user()->id)->get();
        return response()->json(['data' => $data, 'code' => 200, 'message' => 'ok']);
    }

    public function add(Request $request)
    {
        $address = $request->get('address');
        $contact_name = $request->input('contact_name');
        $contact_phone = $request->get('contact_phone');


        $user_id = Auth::user();

        $created_at = time();
        try {
            $data = [
                'user_id' => $user_id,
                'address' => $address,
                'contact_name' => $contact_name,
                'contact_phone' => $contact_phone,
                'created_at' => $created_at
            ];
            DB::table('user_addresses')->insert($data);
            return response()->json(['code' => 200, 'message' => '添加成功']);
        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'message' => $e->getMessage()]);
        }
    }

    public function delete(Request $request)
    {
        $id = $request->get('id');
        try {
            DB::table('user_addresses')
                ->where('id', $id)
                ->where('user_id', Auth::user()->id)
                ->delete();
            return response()->json(['code' => 200, 'message' => '删除成功']);

        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'message' => '删除失败']);
        }
    }


    //
}
