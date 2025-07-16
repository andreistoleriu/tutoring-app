<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ReminderService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register ReminderService as singleton
        $this->app->singleton(ReminderService::class, function ($app) {
            return new ReminderService();
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
