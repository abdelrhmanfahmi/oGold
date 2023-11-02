<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class isClient
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $guard = 'api')
    {
        if (Auth::guard($guard)->check()) {
            if (Auth::user()->type == 'client') {
                return $next($request);
            }
                return response()->json(['message' => 'Unauthorized' , 403]);
        }
            return response()->json(['message' => 'Unauthorized' , 403]);
    }
}
