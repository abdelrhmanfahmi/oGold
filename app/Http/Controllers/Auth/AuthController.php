<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
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
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'type' => $data['type']
            ]);

            //assign role for each user(admin,client,provider)
            if($user->type == 'admin'){
                $user->assignRole('admin');
            }else if($user->type == 'client'){
                $user->assignRole('client');
            }else{
                $user->assignRole('provider');
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
