<?php

namespace App\Services;

use App\Models\Identity;
use App\Models\IdentityEvent;
use Illuminate\Support\Facades\DB;

class EventSourcingService
{
    public function recordEvent(string $uuid, string $eventType, array $context = []): IdentityEvent
    {
        $url = $context['url'] ?? request()?->fullUrl();
        $referer = $context['referer'] ?? request()?->header('referer');

        $event = IdentityEvent::create([
            'uuid' => $uuid,
            'event_type' => $eventType,
            'url' => mb_strcut($url, 0, 255),
            'referer' => mb_strcut($referer ?? '', 0, 255),
            'utm_source' => $context['utm_source'] ?? request()?->query('utm_source'),
            'utm_medium' => $context['utm_medium'] ?? request()?->query('utm_medium'),
            'utm_campaign' => $context['utm_campaign'] ?? request()?->query('utm_campaign'),
            'utm_term' => $context['utm_term'] ?? request()?->query('utm_term'),
            'utm_content' => $context['utm_content'] ?? request()?->query('utm_content'),
            'fbclid' => $context['fbclid'] ?? request()?->query('fbclid'),
            'gclid' => $context['gclid'] ?? request()?->query('gclid'),
            'ttclid' => $context['ttclid'] ?? request()?->query('ttclid'),
            'twclid' => $context['twclid'] ?? request()?->query('twclid'),
        ]);

        Identity::updateOrCreate(
            ['uuid' => $uuid],
            ['last_seen_at' => now()]
        );

        return $event;
    }

    public function getUserJourney(string $uuid, ?int $limit = 100): array
    {
        return IdentityEvent::where('uuid', $uuid)
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get()
            ->toArray();
    }

    public function getFirstTouch(string $uuid): ?IdentityEvent
    {
        return IdentityEvent::where('uuid', $uuid)
            ->orderBy('created_at', 'asc')
            ->first();
    }

    public function getLastTouch(string $uuid): ?IdentityEvent
    {
        return IdentityEvent::where('uuid', $uuid)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function getAttributionData(string $uuid): array
    {
        $first = $this->getFirstTouch($uuid);
        $last = $this->getLastTouch($uuid);

        $allSources = IdentityEvent::where('uuid', $uuid)
            ->whereNotNull('utm_source')
            ->select('utm_source', 'utm_medium', 'utm_campaign', 'created_at')
            ->orderBy('created_at')
            ->get();

        return [
            'uuid' => $uuid,
            'first_touch' => $first ? [
                'utm_source' => $first->utm_source,
                'utm_medium' => $first->utm_medium,
                'utm_campaign' => $first->utm_campaign,
                'url' => $first->url,
                'referer' => $first->referer,
                'timestamp' => $first->created_at,
            ] : null,
            'last_touch' => $last ? [
                'utm_source' => $last->utm_source,
                'utm_medium' => $last->utm_medium,
                'utm_campaign' => $last->utm_campaign,
                'url' => $last->url,
                'referer' => $last->referer,
                'timestamp' => $last->created_at,
            ] : null,
            'all_sources' => $allSources,
            'click_ids' => [
                'fbclid' => $last?->fbclid,
                'gclid' => $last?->gclid,
                'ttclid' => $last?->ttclid,
                'twclid' => $last?->twclid,
            ],
        ];
    }

    public function getStatsBySource(string $utmSource, array $dateRange = []): array
    {
        $query = IdentityEvent::where('utm_source', $utmSource);

        if (!empty($dateRange)) {
            $query->whereBetween('created_at', $dateRange);
        }

        return [
            'total_visits' => $query->count(),
            'unique_visitors' => $query->distinct('uuid')->count('uuid'),
            'purchases' => IdentityEvent::where('utm_source', $utmSource)
                ->where('event_type', 'purchase')
                ->when(!empty($dateRange), fn($q) => $q->whereBetween('created_at', $dateRange))
                ->count(),
        ];
    }

    public function getDailyStats(int $days = 30): array
    {
        return IdentityEvent::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total_events'),
            DB::raw('COUNT(DISTINCT uuid) as unique_visitors'),
            DB::raw("SUM(CASE WHEN event_type = 'purchase' THEN 1 ELSE 0 END) as purchases"),
            DB::raw("SUM(CASE WHEN event_type = 'page_view' THEN 1 ELSE 0 END) as page_views")
        )
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'desc')
            ->get()
            ->toArray();
    }
}
