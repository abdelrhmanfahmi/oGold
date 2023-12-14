<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuyGold extends Model
{
    use HasFactory;
    protected $fillable = ['user_id' , 'volume' , 'symbol'];

    public function client()
    {
        return $this->belongsTo(User::class , 'user_id');
    }
}
