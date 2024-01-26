<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchData extends Model
{
    use HasFactory;
    protected $fillable = ['access_token' , 'partner_id' , 'offer_uuid' , 'oneTimeToken' , 'co_auth' , 'trading_api_token' , 'manager_password' , 'manager_token' , 'manager_id'];
}
