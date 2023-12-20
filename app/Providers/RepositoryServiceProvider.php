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
use App\Repository\Interfaces\FaqRepositoryInterface;
use App\Repository\FaqRepository;
use App\Repository\Interfaces\AddressBookRepositoryInterface;
use App\Repository\AddressBookRepository;
use App\Repository\Interfaces\WithdrawRepositoryInterface;
use App\Repository\WithdrawRepository;
use App\Repository\Interfaces\DepositRepositoryInterface;
use App\Repository\DepositRepository;
use App\Repository\Interfaces\BuyGoldRepositoryInterface;
use App\Repository\BuyGoldRepository;
use App\Repository\Interfaces\SellGoldRepositoryInterface;
use App\Repository\SellGoldRepository;
use App\Repository\Interfaces\BankRepositoryInterface;
use App\Repository\BankRepository;

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
        $this->app->bind(FaqRepositoryInterface::class, FaqRepository::class);
        $this->app->bind(AddressBookRepositoryInterface::class, AddressBookRepository::class);
        $this->app->bind(WithdrawRepositoryInterface::class, WithdrawRepository::class);
        $this->app->bind(DepositRepositoryInterface::class, DepositRepository::class);
        $this->app->bind(BuyGoldRepositoryInterface::class, BuyGoldRepository::class);
        $this->app->bind(SellGoldRepositoryInterface::class, SellGoldRepository::class);
        $this->app->bind(BankRepositoryInterface::class, BankRepository::class);
    }
}
