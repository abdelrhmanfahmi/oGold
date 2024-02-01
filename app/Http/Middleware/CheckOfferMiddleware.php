<?php

namespace App\Http\Middleware;

use App\Models\Offers;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        //check if request url login and user is admin or refinery
        if(str_contains($request->url(), 'api/auth/admin/login')){
            return $next($request);
        }elseif(str_contains($request->url(), 'api/auth/user/info')){
            return $next($request);
        }else{
            dd($request->header('offer_id'));
            $offer = $request->header('offer_id');
            if($offer){
                $checkOffer = Offers::where('offer_id' , $offer)->exists();
                if(!$checkOffer){
                    $message = 'Invalid Offer ID';
                    abort('403', $message);
                }
                return $next($request);
            }else{
                $message = 'Forbidden to proceed!';
                abort('403', $message);
            }
        }
    }
}
