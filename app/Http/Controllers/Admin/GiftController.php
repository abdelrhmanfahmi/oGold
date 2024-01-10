<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\GiftResource;
use App\Models\Gift;
use Illuminate\Http\Request;

class GiftController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        try{
            $gifts = Gift::with('sender')->with('recieved')->orderBy('id' , 'DESC')->get();
            return GiftResource::collection($gifts);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function show($id)
    {
        try{
            $gift = Gift::with('sender')->with('recieved')->whereId($id)->first();
            return GiftResource::make($gift);
        }catch(\Exception $e){
            return $e;
        }
    }
}
