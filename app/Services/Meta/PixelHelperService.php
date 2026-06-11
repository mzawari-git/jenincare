<?php

namespace App\Services\Meta;

use App\Models\MarketingSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PixelHelperService
{
    private FacebookGraphService $graph;

    public function __construct(FacebookGraphService $graph)
    {
        $this->graph = $graph;
    }

    public function verifyPixel(?string $pixelId = null): array
    {
        $pixelId = $pixelId ?? MarketingSetting::get('facebook_pixel_id');
        $accessToken = MarketingSetting::get('facebook_access_token');

        if (!$pixelId || !$accessToken) {
            return ['success' => false, 'message' => 'Pixel ID أو Access Token غير موجود'];
        }

        $this->graph->setUserAccessToken($accessToken);

        $result = $this->graph->get($pixelId, [
            'fields' => 'id,name,code,creation_time,last_fired_time,event_count,app_id',
        ]);

        if (isset($result['id'])) {
            return [
                'success' => true,
                'pixel' => [
                    'id' => $result['id'],
                    'name' => $result['name'] ?? '',
                    'creation_time' => $result['creation_time'] ?? '',
                    'last_fired_time' => $result['last_fired_time'] ?? '',
                    'event_count' => $result['event_count'] ?? 0,
                    'status' => $this->getPixelStatus($result),
                ],
            ];
        }

        return ['success' => false, 'message' => '.Pixel غير موجود أو Access Token غير صالح'];
    }

    public function getPixelEvents(?string $pixelId = null, int $days = 7): array
    {
        $pixelId = $pixelId ?? MarketingSetting::get('facebook_pixel_id');
        $accessToken = MarketingSetting::get('facebook_access_token');

        if (!$pixelId || !$accessToken) {
            return ['success' => false, 'events' => []];
        }

        $this->graph->setUserAccessToken($accessToken);

        $result = $this->graph->get("{$pixelId}/events", [
            'fields' => 'event_name,event_time,user_data',
            'limit' => 100,
        ]);

        $events = [];
        foreach ($result['data'] ?? [] as $event) {
            $events[] = [
                'event_name' => $event['event_name'] ?? '',
                'event_time' => $event['event_time'] ?? '',
            ];
        }

        return ['success' => true, 'events' => $events, 'count' => count($events)];
    }

    public function checkBrowserVsCapi(): array
    {
        $pixelEnabled = MarketingSetting::get('facebook_pixel_enabled', false);
        $capiEnabled = MarketingSetting::get('facebook_capi_enabled', false);

        $browserEvents = Cache::get('pixel_browser_events', []);
        $capiEvents = Cache::get('pixel_capi_events', []);

        return [
            'browser_pixel' => [
                'enabled' => $pixelEnabled,
                'pixel_id' => MarketingSetting::get('facebook_pixel_id'),
                'events_count' => count($browserEvents),
            ],
            'server_capi' => [
                'enabled' => $capiEnabled,
                'events_count' => count($capiEvents),
            ],
            'coverage' => [
                'browser' => $capiEnabled ? round(count($capiEvents) / max(count($browserEvents), 1) * 100) : 0,
            ],
            'recommendations' => $this->getRecommendations($pixelEnabled, $capiEnabled),
        ];
    }

    public function trackEvent(string $eventName): void
    {
        $key = 'pixel_browser_events';
        $events = Cache::get($key, []);
        $events[] = ['event' => $eventName, 'time' => now()->toIso8601String()];
        $events = array_slice($events, -1000);
        Cache::put($key, $events, 3600);
    }

    public function getHealthReport(): array
    {
        $capiLogs = \App\Models\CapiEventLog::where('platform', 'facebook')
            ->where('created_at', '>=', now()->subHours(24))
            ->get();

        $total = $capiLogs->count();
        $success = $capiLogs->where('success', true)->count();
        $failed = $capiLogs->where('success', false)->count();
        $uniqueEvents = $capiLogs->pluck('event_name')->unique()->toArray();

        $expectedEvents = ['PageView', 'ViewContent', 'AddToCart', 'InitiateCheckout', 'Purchase', 'Lead'];
        $missingEvents = array_diff($expectedEvents, $uniqueEvents);

        return [
            'status' => $total > 0 ? ($success / max($total, 1) >= 0.9 ? 'healthy' : 'warning') : 'unknown',
            'total_events_24h' => $total,
            'success_rate' => $total > 0 ? round($success / $total * 100, 1) : 0,
            'unique_event_types' => $uniqueEvents,
            'missing_events' => $missingEvents,
            'recommendations' => $this->getHealthRecommendations($total, $success, $missingEvents),
        ];
    }

    private function getPixelStatus(array $pixel): string
    {
        $lastFired = $pixel['last_fired_time'] ?? null;
        if (!$lastFired) return 'inactive';

        $hoursSinceLastEvent = (now()->timestamp - strtotime($lastFired)) / 3600;

        if ($hoursSinceLastEvent < 24) return 'active';
        if ($hoursSinceLastEvent < 168) return 'stale';
        return 'inactive';
    }

    private function getRecommendations(bool $browser, bool $server): array
    {
        $recs = [];
        if ($browser && !$server) {
            $recs[] = ['type' => 'warning', 'message' => 'فعّل CAPI لتحسين التتبع وتجاوز iOS'];
        }
        if (!$browser && $server) {
            $recs[] = ['type' => 'info', 'message' => 'أضف Browser Pixel لتحسين Deduplication'];
        }
        if (!$browser && !$server) {
            $recs[] = ['type' => 'error', 'message' => 'لا يوجد Browser Pixel أو CAPI مفعّل'];
        }
        return $recs;
    }

    private function getHealthRecommendations(int $total, int $success, array $missing): array
    {
        $recs = [];
        if ($total === 0) {
            $recs[] = ['type' => 'error', 'message' => 'لا توجد أحداث CAPI في آخر 24 ساعة'];
        }
        if ($success / max($total, 1) < 0.9) {
            $recs[] = ['type' => 'warning', 'message' => 'معدل النجاح أقل من 90% - تحقق من Access Token'];
        }
        if (!empty($missing)) {
            $recs[] = ['type' => 'info', 'message' => 'أحداث مفقودة: ' . implode(', ', $missing)];
        }
        return $recs;
    }
}
