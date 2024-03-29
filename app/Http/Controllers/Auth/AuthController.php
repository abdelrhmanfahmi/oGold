<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmEmailMatchRequest;
use App\Http\Requests\ForgetPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetForgetPasswordRequest;
use App\Http\Requests\SendVerificationCodeRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Mail\UserMail;
use App\Models\Offers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Repository\Interfaces\UserRepositoryInterface;
use App\Services\MatchService;
use App\Services\PasswordResetService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(private MatchService $matchService , private UserRepositoryInterface $userRepository , private PasswordResetService $passwordResetService)
    {
        $this->middleware('auth:api', ['except' => ['login','register','forgetPassowrdMatch','verifyEmailConfirmationMatch' , 'resendVerificcationCode']]);
    }

    public function login(LoginRequest $request)
    {
        try{
            $credentials = $request->only('email', 'password');
            $token = Auth::setTTL(60*24*365)->attempt($credentials);

            //check valid token
            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                ], 401);
            }

            //check if this credentials belongs to this user type
            if(Auth::user()->type == 'admin' || Auth::user()->type == 'refinery'){
                $user = Auth::user();

                return response()->json([
                    'status' => 'success',
                    'user' => $user,
                    'token' => $token
                ] , 200);
            }

            if (Auth::user()->type != Request()->type) {
                auth()->logout();
                return response()->json(['message' => 'Unauthorized']);
            }

            //success logged in
            $user = Auth::user();

            if(Auth::user()->offer_uuid == null){
                $offer_id = request()->headers->get('offer');
                $checkIfExists = Offers::where('offer_id' , $offer_id)->exists();
                if($checkIfExists){
                    Auth::user()->update(['offer_uuid' => $offer_id]);
                }else{
                    $firstOffer = Offers::first();
                    Auth::user()->update(['offer_uuid' => $firstOffer->offer_id]);
                }
            }

            //login in match apis
            $this->matchService->loginAccount($credentials);

            return response()->json([
                'status' => 'success',
                'user' => $user,
                'token' => $token
            ] , 200);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function register(RegisterRequest $request)
    {
        try{
            $data = $request->validated();
            //create token with parentId
            $this->matchService->getAccessToken();

            //check offer exists
            $offer_id = request()->headers->get('offer');
            $checkIfExists = Offers::where('offer_id' , $offer_id)->exists();
            if($checkIfExists){
                $data['offer_uuid'] = $offer_id;
                $this->matchService->createUserInMatch($data);
            }else{
                $firstOffer = Offers::first();
                $data['offer_uuid'] = $firstOffer->offer_id;
                $this->matchService->createUserInMatch($data);
            }


            //send verification code of match to new user
            $this->matchService->sendVerificationCode($data['email']);

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

    public function verifyEmailConfirmationMatch(ConfirmEmailMatchRequest $request)
    {
        try{
            $data = $request->validated();
            $isVerified = $this->matchService->confirmEmailVerification($data);
            if($isVerified){
                return response()->json(['message' => 'Email Verified Successfully'], 200);
            }else{
                return response()->json(['message' => 'code is not valid'], 400);
            }

        }catch(\Exception $e){
            return $e;
        }
    }

    public function resendVerificcationCode(SendVerificationCodeRequest $request)
    {
        try{
            $data = $request->validated();
            $this->matchService->sendVerificationCode($data['email']);
            return response()->json(['message' => 'Verification Code Sent Successfully!'] , 200);
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

    // public function forgetPassowrd(ForgetPasswordRequest $request)
    // {
    //     try{
    //         $data = $request->validated();
    //         $token = Str::random(64);
    //         $this->passwordResetService->createPasswordReset($data , $token);
    //         \Mail::to($data['email'])->send(new UserMail($token));
    //         return response()->json(['message'=> 'Check Inbox Please']);
    //     }catch(\Exception $e){
    //         return $e;
    //     }
    // }

    public function forgetPassowrdMatch(ForgetPasswordRequest $request)
    {
        try{
            $data = $request->validated();
            $this->matchService->forgetPassowrdInMatch($data);
            return response()->json(['message'=> 'Check Inbox Please']);
        }catch(\Exception $e){
            return $e;
        }
    }

    // public function resetPassword(ResetForgetPasswordRequest $request)
    // {
    //     try{
    //         $data = $request->validated();
    //         $updatePassword = $this->passwordResetService->checkIfPasswordResetExists($data , Request()->token);

    //         if($updatePassword == 0){
    //             return response()->json(['message' => 'invalid token']);
    //         }else{
    //             $model = $this->userRepository->findByEmail($data['email']);
    //             $this->userRepository->update($model , $data);
    //             $this->passwordResetService->deletePasswordResetRecord($data['email']);
    //             return response()->json(['message' => 'Password Changed Successfully']);
    //         }
    //     }catch(\Exception $e){
    //         return $e;
    //     }
    // }

    public function resetPasswordMatch(ResetForgetPasswordRequest $request)
    {
        try{
            $data = $request->validated();
            $model = $this->userRepository->find(Auth::user()->id);
            if(Hash::check($data['current_password'] , Auth::user()->password)){
                $this->matchService->changePassowrdInMatch($data);
                $this->userRepository->update($model , $data);
                return response()->json(['message' => 'Password Updated Successfully']);
            }else{
                return response()->json(['message' => 'Old Password Invalid'], 422);
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

    // public function refresh()
    // {
    //     return response()->json([
    //         'status' => 'success',
    //         'user' => Auth::user(),
    //         'token' => Auth::refresh()
    //     ]);
    // }

    public function refresh()
    {
        try{
            $isRefreshed = $this->matchService->refreshTokenInMatch();
            if($isRefreshed){
                return response()->json(['message' => 'token Refreshed Successfully' , 'data' => Auth::user()],200);
            }
        }catch(\Exception $e){
            return $e;
        }
    }

    public function getUserInfo()
    {
        try{
            return UserResource::make(Auth::user());
        }catch(\Exception $e){
            return $e;
        }
    }
}
