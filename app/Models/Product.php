<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Filters\Filters;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['name' , 'price' , 'image'];

    public function scopeFilter($query, Filters $filter)
    {
        return $filter->apply($query);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class , 'order_products' , 'product_id' , 'order_id')->withPivot('quantity');
    }
}
