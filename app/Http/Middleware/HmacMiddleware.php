<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HmacMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $header = env('HMAC_PUBLIC_KEY');
        $request_hash = $request->headers->get($header);
        if (!$request_hash) {
            $message = 'Header `' . $header . '` missing.';
            abort('403', $message);
        }

        $body = $request->all();
        $url = request()->url();
        $verb = $request->method();
        $md5 = md5(json_encode($body));

        $string = $verb . PHP_EOL . $url . PHP_EOL . $md5;

        $hash = hash_hmac('SHA256', $string, env('HMAC_SECRET_KEY'));
        $base64_hash = base64_encode($hash);

        if ($base64_hash !== $request_hash) {
            $message = 'Invalid `' . $header . '` Header';
            abort('403', $message);
        }
        return $next($request);
    }
}
