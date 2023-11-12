<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repository\Interfaces\UserRepositoryInterface;
use App\Repository\UserRepository;
use App\Repository\Interfaces\SettingRepositoryInterface;
use App\Repository\SettingRepository;
use App\Repository\Interfaces\ProductRepositoryInterface;
use App\Repository\ProductRepository;
use App\Repository\Interfaces\OrderRepositoryInterface;
use App\Repository\OrderRepository;
use App\Repository\Interfaces\DeliveryRepositoryInterface;
use App\Repository\DeliveryRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(SettingRepositoryInterface::class, SettingRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(DeliveryRepositoryInterface::class, DeliveryRepository::class);
    }
}
