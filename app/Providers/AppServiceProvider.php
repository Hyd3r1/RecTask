<?php

namespace App\Providers;

use App\Http\Integrations\LocationIq;
use App\Http\Integrations\OpenMeteo;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(OpenMeteo::class, function () {
            return OpenMeteo::getInstance();
        });

        $this->app->singleton(LocationIq::class, function () {
            return LocationIq::getInstance();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
