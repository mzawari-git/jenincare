<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class HorizonServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (!class_exists(\Laravel\Horizon\Horizon::class)) {
            return;
        }

        $this->app->register(\Laravel\Horizon\HorizonServiceProvider::class);
    }

    public function boot(): void
    {
        if (!class_exists(\Laravel\Horizon\Horizon::class)) {
            return;
        }

        \Illuminate\Support\Facades\Gate::define('viewHorizon', function ($user = null) {
            return true;
        });
    }
}
