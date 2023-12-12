<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckMatchAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        if(Auth::user()->co_auth != null && Auth::user()->trading_api_token != null){
            return $next($request);
        }
        return response()->json(['message' => 'Check Mail Verification And Login Again'], 403);
    }
}
