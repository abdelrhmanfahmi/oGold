<?php

namespace App\Models;

use App\Filters\Filters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;
    protected $fillable = ['status' , 'total_price' , 'order_id'];

    public function scopeFilter($query, Filters $filter)
    {
        return $filter->apply($query);
    }

    public function order()
    {
        return $this->belongsTo(Order::class , 'order_id');
    }
}
