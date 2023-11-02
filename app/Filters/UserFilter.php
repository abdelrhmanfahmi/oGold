<?php

namespace App\Filters;

use Carbon\Carbon;

class UserFilter extends Filters
{
    protected $var_filters = [
        'name', 'email', 'type', 'created_at_from', 'created_at_to'
    ];

    public function name($value)
    {
        return $this->builder->where('first_name', 'like', "%$value%");
    }
    public function email($value)
    {
        return $this->builder->where('email', 'like', "%$value%");
    }
    
    public function type($value)
    {
        return $this->builder->where('type',  $value);
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