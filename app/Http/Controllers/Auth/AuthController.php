<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgetPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetForgetPasswordRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Mail\UserMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Repository\Interfaces\UserRepositoryInterface;
use App\Services\MatchService;
use App\Services\PasswordResetService;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(private MatchService $matchService , private UserRepositoryInterface $userRepository , private PasswordResetService $passwordResetService)
    {
        $this->middleware('auth:api', ['except' => ['login','register','forgetPassowrd','resetPassword']]);
    }

    public function login(LoginRequest $request)
    {
        try{
            $credentials = $request->only('email', 'password');
            $token = Auth::attempt($credentials);

            //check valid token
            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                ], 401);
            }

            //login in match apis
            $this->matchService->loginAccount($credentials);

            //check if this credentials belongs to this user type
            if (Auth::user()->type != Request()->type) {
                auth()->logout();
                return response()->json(['message' => 'Unauthorized']);
            }

            //success logged in
            $user = Auth::user();
            return response()->json([
                'status' => 'success',
                'user' => $user,
                'token' => $token
            ]);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function register(RegisterRequest $request)
    {
        try{
            $data = $request->validated();
            //create token with parentId and get offerUuid
            $match_data = $this->matchService->getAccessToken();
            $this->matchService->getOfferUUID($match_data);
            $this->matchService->createUserInMatch($data);
            
            //create user
            $user = $this->userRepository->create($data);

            //assign role for each user(admin,client,provider)
            if($user->type == 'admin'){
                $user->assignRole('admin');
            }else if($user->type == 'client'){
                $user->assignRole('client');
            }else{
                $user->assignRole('refinery');
            }

            $token = Auth::login($user);
            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully',
                'user' => $user,
                'token' => $token
            ]);
            
        }catch(\Exception $e){
            return $e;
        }
    }

    public function updateUser(UpdateUserRequest $request , $id)
    {
        try{
            $data = $request->validated();
            $model = $this->userRepository->find($id);
            $token = $this->matchService->getAccessToken();
            $this->matchService->updateAccount($data , $model , $token);
            $user = $this->userRepository->update($model , $data);
            return response()->json(['data' => $user] , 200);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function forgetPassowrd(ForgetPasswordRequest $request)
    {
        try{
            $data = $request->validated();
            $token = Str::random(64);
            $this->passwordResetService->createPasswordReset($data , $token);
            \Mail::to($data['email'])->send(new UserMail($token));
            return response()->json(['message'=> 'Check Inbox Please']);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function resetPassword(ResetForgetPasswordRequest $request)
    {
        try{
            $data = $request->validated();
            $updatePassword = $this->passwordResetService->checkIfPasswordResetExists($data , Request()->token);
  
            if($updatePassword == 0){
                return response()->json(['message' => 'invalid token']);
            }else{
                $model = $this->userRepository->findByEmail($data['email']);
                $this->userRepository->update($model , $data);
                $this->passwordResetService->deletePasswordResetRecord($data['email']);
                return response()->json(['message' => 'Password Changed Successfully']);
            }
        }catch(\Exception $e){
            return $e;
        }
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'token' => Auth::refresh()
        ]);
    }
}