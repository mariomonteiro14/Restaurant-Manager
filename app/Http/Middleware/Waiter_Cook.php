<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Waiter_Cook
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
        if(Auth::user()->isWaiter() || Auth::user()->isCook()){
            return $next($request);
        }
        return response()->json(['message' => 'Unauthorized'], 403);
    }
}
