<?php

namespace App\Http\Middleware;

use App\Admin;
use Closure;

class AdminAuthenticate
{
    /**
     * Handle an incoming request.
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->get('token');
        $admin = Admin::where('token', $token)->first();
        if (!$admin) {
              return response()->json(['data' => [], 'code' => 401, 'message' => 'Unauthorized.']);
        }

        return $next($request);
    }
}
