<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Livewire::addPersistentMiddleware([
            \App\Http\Middleware\UserVinculed::class,
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // force https in production
        if (app()->environment('production')) {
            \URL::forceScheme('https');
        }

        // disable ssl verification in local
        if (app()->environment('local')) {
            Http::globalOptions([
                'verify' => false,
            ]);
        }
    }
}
