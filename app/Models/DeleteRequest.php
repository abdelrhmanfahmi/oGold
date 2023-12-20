<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeleteRequest extends Model
{
    use HasFactory;
    protected $fillable = ['status' , 'user_id'];

    public function client()
    {
        return $this->belongsTo(User::class , 'user_id');
    }
}
