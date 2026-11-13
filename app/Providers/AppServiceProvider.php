<?php

namespace App\Providers;

use App\Models\AccountMovement;
use App\Models\StockMovement;
use App\Observers\AccountMovementObserver;
use App\Observers\StockMovementObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        AccountMovement::observe(AccountMovementObserver::class);
        StockMovement::observe(StockMovementObserver::class);
    }
}
