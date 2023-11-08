<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PasswordResetService {
    
    public function createPasswordReset($data , $token)
    {
        try{
            DB::table('password_reset_tokens')->updateOrInsert(
            [
                'email' => $data['email']
            ],
            [
              'email' => $data['email'],
              'token' => $token,
              'created_at' => Carbon::now()
            ]);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function checkIfPasswordResetExists($data , $token)
    {
        try{
            $recordExists = DB::table('password_reset_tokens')
                    ->where([
                    'email' => $data['email'], 
                    'token' => $token
                    ])
                    ->first();
            if(!$recordExists){
                return 0;
            }
            return 1;
        }catch(\Exception $e){
            return $e;
        }
    }
    public function deletePasswordResetRecord($email) 
    {
        DB::table('password_reset_tokens')->where(['email'=> $email])->delete();
    }
}