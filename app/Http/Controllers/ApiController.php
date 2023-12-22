<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckUserExistsRequest;
use App\Http\Resources\UserResource;
use App\Repository\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {}
    public function getUserData(CheckUserExistsRequest $request)
    {
        try{
            $data = $request->validated();
            $user = $this->userRepository->findByPhone($data['phone']);
            return UserResource::make($user);
        }catch(\Exception $e){
            return $e;
        }
    }
}
