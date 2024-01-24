<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckUserExistsRequest;
use App\Http\Resources\CatalogDataResource;
use App\Http\Resources\GiftUserResource;
use App\Http\Resources\UserResource;
use App\Models\Catalog;
use App\Repository\Interfaces\CatalogRepositoryInterface;
use App\Repository\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function __construct(private UserRepositoryInterface $userRepository , private CatalogRepositoryInterface $catalogRepository)
    {}
    public function getUserData(CheckUserExistsRequest $request)
    {
        try{
            $data = $request->validated();
            $user = $this->userRepository->findByPhone($data['phone']);
            return GiftUserResource::make($user);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function getCatalogsData($uuid)
    {
        try{
            $relations = ['products'];
            $catalog = $this->catalogRepository->findByUUID($uuid , $relations);
            return CatalogDataResource::make($catalog);
        }catch(\Exception $e){
            return $e;
        }
    }
}
