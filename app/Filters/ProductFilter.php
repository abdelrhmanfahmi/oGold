<?php

namespace App\Filters;

use Carbon\Carbon;

class ProductFilter extends Filters
{
    protected $var_filters = [
        'name' , 'is_active' , 'created_at_from', 'created_at_to'
    ];

    public function name($value)
    {
        return $this->builder->where('name', 'like', "%$value%");
    }
    public function is_active($value)
    {
        return $this->builder->where('is_active', '=', $value);
    }
    public function created_at_from($value)
    {
        return $this->builder->where('created_at', ">=", new Carbon($value));
    }
    public function created_at_to($value)
    {
        return $this->builder->where('created_at', "<=", new Carbon($value));
    }
}