<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\FirestoreService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register FirestoreService as singleton
        $this->app->singleton(FirestoreService::class, function ($app) {
            return new FirestoreService();
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
