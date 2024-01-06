<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gift extends Model
{
    use HasFactory;
    protected $fillable = ['volume' , 'sender_user_id' , 'recieved_user_id' , 'message' , 'client_order_id'];

    public function sender()
    {
        return $this->belongsTo(User::class , 'sender_user_id');
    }

    public function recieved()
    {
        return $this->belongsTo(User::class , 'recieved_user_id');
    }
}
