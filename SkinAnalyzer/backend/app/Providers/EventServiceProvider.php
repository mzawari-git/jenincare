<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \App\Events\ScanCreated::class => [
            \App\Listeners\SendScanNotification::class,
        ],
        \App\Events\ScanApproved::class => [
            \App\Listeners\NotifyUserScanApproved::class,
        ],
        \App\Events\QuotaExceeded::class => [
            \App\Listeners\HandleQuotaFailover::class,
        ],
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
