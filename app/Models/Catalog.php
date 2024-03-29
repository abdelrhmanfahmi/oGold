<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Catalog extends Model
{
    use HasFactory;
    protected $fillable = ['name' , 'preimum_fees' , 'uuid' , 'status'];

    public function products()
    {
        return $this->belongsToMany(Product::class , 'catalog_products' , 'catalog_id' , 'product_id')->withPivot('total_price');
    }
}
