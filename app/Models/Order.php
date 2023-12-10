<?php

namespace App\Models;

use App\Filters\Filters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = ['user_id' , 'address_book_id' , 'status' , 'is_approved' , 'total'];

    public function scopeFilter($query, Filters $filter)
    {
        return $filter->apply($query);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class , 'order_products' , 'order_id' , 'product_id')->withPivot('quantity');
    }

    public function client()
    {
        return $this->belongsTo(User::class , 'user_id');
    }

    public function address_book()
    {
        return $this->belongsTo(AddressBook::class , 'address_book_id');
    }
}
