<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellGold extends Model
{
    use HasFactory;
    protected $fillable = ['user_id' , 'volume' , 'symbol' , 'sell_price'];

    public function client()
    {
        return $this->belongsTo(User::class , 'user_id');
    }
}
