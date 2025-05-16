<?php

namespace App\Providers;

use App\Services\TmdbService;
use Illuminate\Support\ServiceProvider;

class TmdbServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TmdbService::class, function ($app) {
            return new TmdbService();
        });
        
        // Eliminamos el registro de MovieService
    }

    public function boot(): void
    {
        //
    }
}