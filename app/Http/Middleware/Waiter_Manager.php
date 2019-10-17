<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Waiter_Manager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(Auth::user()->isManager() || Auth::user()->isWaiter()){
            return $next($request);
        }
        return response()->json(['message' => 'Unauthorized'], 403);
    }
}
