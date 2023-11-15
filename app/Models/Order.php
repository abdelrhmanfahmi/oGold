<?php

namespace App\Models;

use App\Filters\Filters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = ['user_id'];

    public function scopeFilter($query, Filters $filter)
    {
        return $filter->apply($query);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class , 'order_products' , 'order_id' , 'product_id')->withPivot('quantity' , 'order_position_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class , 'user_id');
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }
}
