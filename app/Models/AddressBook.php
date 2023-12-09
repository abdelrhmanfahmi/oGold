<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddressBook extends Model
{
    use HasFactory;
    protected $fillable = ['country' , 'city' , 'address' , 'user_id'];

    public function client()
    {
        return $this->belongsTo(User::class , 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
