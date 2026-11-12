<?php

namespace App\Providers;

use App\Models\AccountMovement;
use App\Observers\AccountMovementObserver;
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
    }
}
