<?php

namespace App\Http\Middleware;

use App\Models\Offers;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOfferMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $offer = $request->headers->get('offer_id');
        if($offer){
            $checkOffer = Offers::where('offer_id' , $offer)->exists();
            if(!$checkOffer){
                $message = 'Invalid Offer_id';
                abort('403', $message);
            }
            return $next($request);
        }else{
            $message = 'Forbiden to proceed!';
            abort('403', $message);
        }
    }
}
