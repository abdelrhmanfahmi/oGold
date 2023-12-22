<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Withdraw;
use Illuminate\Auth\Access\Response;

class BankPolicy
{
    public function store(User $user)
    {
        $data = Request()->all();
        $validBanks = $user->bank_details()->pluck('id')->toArray();
        if(in_array($data['bank_details_id'] , $validBanks)){
            return Response::allow();
        }
        return Response::deny('this user cannot use this bank is not assigned to him !');
    }
}
