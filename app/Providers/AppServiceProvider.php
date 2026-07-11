<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use App\Repositories\DashboardRepository;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\OrderRepository;
use App\Repositories\Contracts\MenuRepositoryInterface;
use App\Repositories\MenuRepository;
use App\Repositories\Contracts\IngredientRepositoryInterface;
use App\Repositories\IngredientRepository;
use App\Repositories\Contracts\RecipeRepositoryInterface;
use App\Repositories\RecipeRepository;
use App\Repositories\Contracts\WarehouseRepositoryInterface;
use App\Repositories\WarehouseRepository;
use App\Repositories\Contracts\TableRepositoryInterface;
use App\Repositories\TableRepository;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\CustomerRepository;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Repositories\PaymentRepository;
use App\Repositories\Contracts\DiscountRepositoryInterface;
use App\Repositories\DiscountRepository;
use App\Repositories\Contracts\ReportRepositoryInterface;
use App\Repositories\ReportRepository;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Repositories\NotificationRepository;
use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Repositories\SettingRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(DashboardRepositoryInterface::class, DashboardRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(MenuRepositoryInterface::class, MenuRepository::class);
        $this->app->bind(IngredientRepositoryInterface::class, IngredientRepository::class);
        $this->app->bind(RecipeRepositoryInterface::class, RecipeRepository::class);
        $this->app->bind(WarehouseRepositoryInterface::class, WarehouseRepository::class);
        $this->app->bind(TableRepositoryInterface::class, TableRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        $this->app->bind(DiscountRepositoryInterface::class, DiscountRepository::class);
        $this->app->bind(ReportRepositoryInterface::class, ReportRepository::class);
        $this->app->bind(NotificationRepositoryInterface::class, NotificationRepository::class);
        $this->app->bind(SettingRepositoryInterface::class, SettingRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
