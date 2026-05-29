<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LtvMultiplierService
{
    private string $mlServiceUrl;
    private array $defaultMultipliers;

    public function __construct()
    {
        $this->mlServiceUrl = config('services.ltv.base_url', env('LTV_SERVICE_URL', 'http://localhost:8000'));
        $this->defaultMultipliers = [
            'b2b' => config('tracking.ltv.multipliers.b2b', 1.5),
            'b2c' => config('tracking.ltv.multipliers.b2c', 1.0),
            'one_time' => config('tracking.ltv.multipliers.one_time', 0.8),
        ];
    }

    public function getMultiplier(string $segment, ?string $platform = null): float
    {
        $multipliers = $this->defaultMultipliers;

        if ($platform) {
            $platformMultipliers = config("tracking.ltv.platform_multipliers.{$platform}");
            if ($platformMultipliers) {
                $multipliers = array_merge($multipliers, $platformMultipliers);
            }
        }

        return $multipliers[$segment] ?? 1.0;
    }

    public function predictLtv(float $aov, int $categoryEncoded = 0, array $extra = []): array
    {
        try {
            $response = Http::timeout(3)->post("{$this->mlServiceUrl}/predict-ltv", [
                'aov' => $aov,
                'category_encoded' => $categoryEncoded,
                'cod_ratio' => $extra['cod_ratio'] ?? 0,
                'location_encoded' => $extra['location_encoded'] ?? 0,
                'device_encoded' => $extra['device_encoded'] ?? 0,
                'day_of_week' => $extra['day_of_week'] ?? (int) now()->dayOfWeek,
                'month' => $extra['month'] ?? (int) now()->month,
                'channel_encoded' => $extra['channel_encoded'] ?? 0,
            ]);

            if ($response->successful()) {
                return $response->json('data', [
                    'ltv_30d' => 0,
                    'ltv_90d' => 0,
                    'ltv_365d' => 0,
                    'segment' => 'one_time',
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('LTV service unavailable', ['error' => $e->getMessage()]);
        }

        return $this->estimateLocal($aov);
    }

    public function applyMultiplier(float $value, string $segment, ?string $platform = null): array
    {
        $multiplier = $this->getMultiplier($segment, $platform);

        return [
            'original_value' => $value,
            'segment' => $segment,
            'multiplier' => $multiplier,
            'adjusted_value' => round($value * $multiplier, 2),
        ];
    }

    public function adjustEventPayload(array $payload, string $segment): array
    {
        $platform = $payload['platform'] ?? null;
        $value = (float) ($payload['data']['value'] ?? 0);

        if ($value <= 0) {
            return $payload;
        }

        $adjustment = $this->applyMultiplier($value, $segment, $platform);

        if ($adjustment['adjusted_value'] !== $value) {
            $payload['data']['value'] = $adjustment['adjusted_value'];
            $payload['data']['_original_value'] = $adjustment['original_value'];
            $payload['data']['_ltv_multiplier'] = $adjustment['multiplier'];
            $payload['data']['_ltv_segment'] = $adjustment['segment'];
        }

        return $payload;
    }

    private function estimateLocal(float $aov): array
    {
        if ($aov > 500) {
            $segment = 'b2b';
            $ltv30 = $aov * 2.0;
        } elseif ($aov > 100) {
            $segment = 'b2c';
            $ltv30 = $aov * 1.2;
        } else {
            $segment = 'one_time';
            $ltv30 = $aov * 1.0;
        }

        return [
            'ltv_30d' => round($ltv30, 2),
            'ltv_90d' => round($ltv30 * 2.5, 2),
            'ltv_365d' => round($ltv30 * 6.0, 2),
            'segment' => $segment,
        ];
    }
}
