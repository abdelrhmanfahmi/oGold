<?php

namespace App\Http\Controllers\Refinery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderDeliveryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    
    public function checkOrderApproved()
    {
        try{
            dd('refinery');
        }catch(\Exception $e){
            return $e;
        }
    }
}
