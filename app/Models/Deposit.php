<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;
    protected $fillable = ['user_id' , 'amount' , 'currency' , 'status'];

    public function client()
    {
        return $this->belongsTo(User::class , 'user_id');
    }
}
