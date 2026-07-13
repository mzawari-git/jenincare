<?php

namespace App\Listeners;

use App\Enums\EngineType;
use App\Events\QuotaExceeded;
use App\Models\AIProvider;
use Illuminate\Support\Facades\Log;

class HandleQuotaFailover
{
    public function handle(QuotaExceeded $event): void
    {
        $exhaustedProvider = $event->provider;

        Log::warning('AI provider quota exceeded. Initiating failover.', [
            'provider' => $exhaustedProvider->name,
            'driver_key' => $exhaustedProvider->driver_key,
            'quota_used' => $exhaustedProvider->quota_used,
            'quota_limit' => $exhaustedProvider->quota_limit,
        ]);

        if ($exhaustedProvider->engine_type === EngineType::STRUCTURED->value) {
            $this->failoverToNative($exhaustedProvider);
        } else {
            $this->failoverToNextActive($exhaustedProvider);
        }
    }

    private function failoverToNative(AIProvider $exhaustedProvider): void
    {
        $nativeProvider = AIProvider::where('driver_key', 'native')
            ->where('is_active', true)
            ->where('id', '!=', $exhaustedProvider->id)
            ->first();

        if ($nativeProvider && $nativeProvider->hasQuotaAvailable()) {
            Log::info('Failover: Switching to Native Skin AI.', [
                'from' => $exhaustedProvider->driver_key,
                'to' => 'native',
            ]);

            $exhaustedProvider->update(['is_active' => false]);
            $nativeProvider->update(['is_active' => true]);
        } else {
            Log::error('Failover failed: Native engine unavailable.', [
                'exhausted_provider' => $exhaustedProvider->driver_key,
            ]);
        }
    }

    private function failoverToNextActive(AIProvider $exhaustedProvider): void
    {
        $fallback = AIProvider::where('engine_type', $exhaustedProvider->engine_type)
            ->where('id', '!=', $exhaustedProvider->id)
            ->where('is_active', true)
            ->first();

        if ($fallback && $fallback->hasQuotaAvailable()) {
            Log::info('Failover: Switching to next active provider.', [
                'from' => $exhaustedProvider->driver_key,
                'to' => $fallback->driver_key,
            ]);

            $exhaustedProvider->update(['is_active' => false]);
            $fallback->update(['is_active' => true]);
        } else {
            Log::error('Failover failed: No alternative provider available.', [
                'exhausted_provider' => $exhaustedProvider->driver_key,
            ]);
        }
    }
}
