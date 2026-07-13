<?php

namespace App\Providers;

use App\Http\Middleware\EncryptScanImages;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class SkinAnalyzerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/skinanalyzer.php',
            'skinanalyzer'
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/skinanalyzer.php' => config_path('skinanalyzer.php'),
        ], 'skinanalyzer-config');

        $this->registerMiddlewareAliases();
        $this->registerRoutes();
    }

    private function registerMiddlewareAliases(): void
    {
        $router = $this->app['router'];

        $router->aliasMiddleware('admin', \App\Http\Middleware\AdminMiddleware::class);
        $router->aliasMiddleware('encrypt.scan.images', EncryptScanImages::class);
    }

    private function registerRoutes(): void
    {
        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/admin')
            ->name('admin.')
            ->group(base_path('routes/admin.php'));

    }
}
