<?php

namespace App\Http\Middleware;

use App\Models\Offers;
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
            // $offer_id = $request->headers->get('offer_id');
            $offer = Offers::first();
            $header = "signature";
            $request_hash = $request->headers->get($header);
            if (!$request_hash) {
                $message = 'Header `' . $header . '` missing.';
                abort('403', $message);
            }

            $body = $request->all();
            $body['offer_id'] = $offer->offer_id;
            $data = json_encode($body);

            $hash = hash_hmac('SHA256', $data, $offer->secret_key);

            if ($hash !== $request_hash) {
                $message = 'Invalid `' . $header . '` Header';
                abort('403', $message);
            }

        return $next($request);
    }
}
