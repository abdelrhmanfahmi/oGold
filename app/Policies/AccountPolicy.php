<?php

namespace App\Policies;

use App\Models\DeleteRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AccountPolicy
{
    public function store(User $user)
    {
    }
}
