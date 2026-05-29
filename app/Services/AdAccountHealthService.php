<?php

namespace App\Services;

use App\Models\CapiEventLog;
use App\Models\TriggerWord;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AdAccountHealthService
{
    public function computeScore(string $platform): array
    {
        $cacheKey = "ad_health_{$platform}";
        $ttl = config('tracking.health.check_interval_minutes', 60) * 60;

        return Cache::remember($cacheKey, $ttl, function () use ($platform) {
            $score = 100;
            $signals = [];

            $rejectionRate = $this->getRejectionRate($platform);
            $score -= $rejectionRate * config('tracking.health.rejection_weight', 30);
            $signals['rejection_rate'] = $rejectionRate;

            $errorRate = $this->getErrorRate($platform);
            $score -= $errorRate * config('tracking.health.error_weight', 25);
            $signals['error_rate'] = $errorRate;

            $duplicateRate = $this->getDuplicateRate($platform);
            $score -= $duplicateRate * config('tracking.health.duplicate_weight', 15);
            $signals['duplicate_rate'] = $duplicateRate;

            $sanitizationRate = $this->getSanitizationRate($platform);

            if ($sanitizationRate > 0.5) {
                $score -= ($sanitizationRate - 0.5) * 50 * (config('tracking.health.sanitization_weight', 10) / 10);
            }
            $signals['sanitization_rate'] = $sanitizationRate;

            $score = max(0, min(100, round($score)));
            $signals['final_score'] = $score;

            $this->logHealthCheck($platform, $score, $signals);

            if ($score < config('tracking.health.alert_threshold', 50)) {
                Log::warning("Ad account health alert for {$platform}", [
                    'score' => $score,
                    'signals' => $signals,
                    'threshold' => config('tracking.health.alert_threshold', 50),
                ]);
            }

            return [
                'platform' => $platform,
                'score' => $score,
                'signals' => $signals,
                'status' => $score >= 80 ? 'healthy' : ($score >= 50 ? 'warning' : 'critical'),
                'checked_at' => now(),
            ];
        });
    }

    public function getScore(string $platform): array
    {
        $cacheKey = "ad_health_{$platform}";
        $cached = Cache::get($cacheKey);

        if ($cached) {
            return $cached;
        }

        return $this->computeScore($platform);
    }

    public function getAllScores(): array
    {
        $platforms = ['facebook', 'tiktok', 'google', 'snapchat', 'pinterest', 'twitter', 'linkedin'];
        $scores = [];

        foreach ($platforms as $platform) {
            $scores[$platform] = $this->getScore($platform);
        }

        return $scores;
    }

    private function getRejectionRate(string $platform): float
    {
        $total = CapiEventLog::where('platform', $platform)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        if ($total === 0) return 0;

        $rejected = CapiEventLog::where('platform', $platform)
            ->where('success', false)
            ->whereIn('status_code', [400, 401, 403])
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        return $rejected / max($total, 1);
    }

    private function getErrorRate(string $platform): float
    {
        $total = CapiEventLog::where('platform', $platform)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        if ($total === 0) return 0;

        $errors = CapiEventLog::where('platform', $platform)
            ->where('success', false)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        return $errors / max($total, 1);
    }

    private function getDuplicateRate(string $platform): float
    {
        $total = CapiEventLog::where('platform', $platform)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        if ($total === 0) return 0;

        $duplicates = CapiEventLog::where('platform', $platform)
            ->where('error_message', 'LIKE', '%duplicate%')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        return $duplicates / max($total, 1);
    }

    private function getSanitizationRate(string $platform): float
    {
        $total = CapiEventLog::where('platform', $platform)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        if ($total === 0) return 0;

        $sanitized = CapiEventLog::where('platform', $platform)
            ->where('response', 'LIKE', '%sanitized%')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        return $sanitized / max($total, 1);
    }

    private function logHealthCheck(string $platform, int $score, array $signals): void
    {
        try {
            \App\Models\HealthLog::create([
                'platform' => $platform,
                'score' => $score,
                'signals' => $signals,
                'checked_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Could not log health check', ['error' => $e->getMessage()]);
        }
    }
}
