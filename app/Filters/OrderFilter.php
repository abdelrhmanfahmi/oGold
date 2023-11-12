<?php

namespace App\Filters;

use Carbon\Carbon;

class OrderFilter extends Filters
{
    protected $var_filters = [
        'payment_type','address' , 'created_at_from', 'created_at_to'
    ];

    public function payment_type($value)
    {
        return $this->builder->where('payment_type', "=", $value);
    }

    public function address($value)
    {
        return $this->builder->where('address', 'like', "%$value%");
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