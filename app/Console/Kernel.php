<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('ads:sync-all --platform=meta')
            ->hourly()
            ->withoutOverlapping(10)
            ->onOneServer()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/ads-sync.log'));

        $schedule->command('ads:sync-all --platform=all')
            ->dailyAt('03:00')
            ->withoutOverlapping(30)
            ->onOneServer()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/ads-sync.log'));
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
