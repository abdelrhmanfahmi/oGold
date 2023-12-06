<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Repository\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
        $this->middleware('auth:api');
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try{
            $data = $request->validated();
            $model = $this->userRepository->find(Auth::user()->id);
            if(Hash::check($data['old_password'] , Auth::user()->password)){
                $this->userRepository->update($model , $data);
                return response()->json(['message' => 'Password Updated Successfully']);
            }else{
                return response()->json(['message' => 'Old Password Invalid'], 422);
            }
        }catch(\Exception $e){
            return $e;
        }
    }
}
