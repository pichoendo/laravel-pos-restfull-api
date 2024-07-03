<?php

namespace App\Providers;

use App\Services\CodeGeneratorService;
use App\Services\ItemStockService;
use App\Services\MemberPointLogService;
use App\Services\SalesService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ItemStockService::class, function ($app) {
            return new ItemStockService();
        });

        $this->app->singleton(CodeGeneratorService::class, function ($app) {
            return new CodeGeneratorService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
