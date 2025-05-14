<?php

namespace App\Providers;

use App\Services\MovieService;
use App\Services\TmdbService;
use Illuminate\Support\ServiceProvider;

class TmdbServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TmdbService::class, function ($app) {
            return new TmdbService();
        });
        
        $this->app->singleton(MovieService::class, function ($app) {
            return new MovieService($app->make(TmdbService::class));
        });
    }

    public function boot(): void
    {
        //
    }
}