<?php

namespace App\Filters;

use Carbon\Carbon;

class DeliveryFilter extends Filters
{
    protected $var_filters = [
        'status' , 'is_approved' , 'created_at_from', 'created_at_to'
    ];

    public function status($value)
    {
        return $this->builder->where('status', '=', $value);
    }

    public function is_approved($value)
    {
        return $this->builder->where('is_approved', '=', $value);
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