<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    protected $fillable = [
        'key' ,
        'value' ,
        'image' ,
        'oGold-name' ,
        'oGold-phone' ,
        'oGold-facebook-link' ,
        'oGold-telegram-link' ,
        'oGold-instagram-link' ,
        'oGold-linkedin-link' ,
        'oGold-whatsapp-link'
    ];
}
