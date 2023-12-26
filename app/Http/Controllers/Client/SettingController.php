<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\SettingResource;
use App\Repository\Interfaces\SettingRepositoryInterface;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(private SettingRepositoryInterface $settingRepository)
    {

    }
    public function index()
    {
        try{
            // $paginate = Request()->paginate ?? true;
            $paginate = false;
            $count = Request()->count ?? 10;
            $settings = $this->settingRepository->all($count , $paginate);
            return SettingResource::collection($settings);
        }catch(\Exception $e){
            return $e;
        }
    }
}
