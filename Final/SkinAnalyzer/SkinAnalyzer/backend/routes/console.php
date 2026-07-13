<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Support\Facades\Schedule;

Schedule::command('skinanalyzer:prune-expired-pins')->hourly();

Schedule::call(function () {
    $providers = \App\Models\AIProvider::where('is_active', true)->get();

    foreach ($providers as $provider) {
        if (! $provider->hasQuotaAvailable()) {
            \Illuminate\Support\Facades\Log::warning('Daily quota check: Provider exhausted.', [
                'provider' => $provider->name,
                'quota_used' => $provider->quota_used,
                'quota_limit' => $provider->quota_limit,
            ]);

            event(new \App\Events\QuotaExceeded($provider));
        }
    }
})->dailyAt('06:00')->name('skinanalyzer:check-quotas');

Schedule::command('queue:prune-failed --hours=72')->dailyAt('02:00');
