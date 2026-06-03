<?php

namespace App\Providers;

use App\Events\ScanCreated;
use App\Events\ScanApproved;
use App\Events\QuotaExceeded;
use App\Listeners\SendScanNotification;
use App\Listeners\NotifyUserScanApproved;
use App\Listeners\HandleQuotaFailover;
use App\Http\Middleware\EncryptScanImages;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;

class SkinAnalyzerServiceProvider extends ServiceProvider
{
    protected $listen = [
        ScanCreated::class => [
            SendScanNotification::class,
        ],
        ScanApproved::class => [
            NotifyUserScanApproved::class,
        ],
        QuotaExceeded::class => [
            HandleQuotaFailover::class,
        ],
    ];

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

        $this->registerEventListeners();
        $this->registerMiddlewareAliases();
        $this->registerRoutes();
    }

    private function registerEventListeners(): void
    {
        Event::listen(
            ScanCreated::class,
            SendScanNotification::class,
        );

        Event::listen(
            ScanApproved::class,
            NotifyUserScanApproved::class,
        );

        Event::listen(
            QuotaExceeded::class,
            HandleQuotaFailover::class,
        );
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
