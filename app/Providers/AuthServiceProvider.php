<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\DeleteRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Withdraw;
use App\Policies\AccountPolicy;
use App\Policies\BankPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        Order::class => OrderPolicy::class,
        DeleteRequest::class => AccountPolicy::class,
        Withdraw::class => BankPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
