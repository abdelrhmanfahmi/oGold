<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankDetails extends Model
{
    use HasFactory;
    protected $fillable = ['bank_name' , 'bank_address' , 'bank_swift_code' , 'bank_account_num' , 'bank_account_name' , 'user_id'];

    public function client()
    {
        return $this->belongsTo(User::class , 'user_id');
    }

    public function withdraws()
    {
        return $this->hasMany(Withdraw::class);
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }
}
