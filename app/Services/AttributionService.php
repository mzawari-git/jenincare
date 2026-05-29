<?php

namespace App\Services;

use App\Models\Identity;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class AttributionService
{
    public function __construct(
        private EventSourcingService $eventSourcingService,
    ) {}

    public function getAttribution(string $uuid): array
    {
        return $this->eventSourcingService->getAttributionData($uuid);
    }

    public function getFirstTouchSource(string $uuid): ?string
    {
        $first = $this->eventSourcingService->getFirstTouch($uuid);
        return $first?->utm_source;
    }

    public function getFirstTouchCampaign(string $uuid): ?string
    {
        $first = $this->eventSourcingService->getFirstTouch($uuid);
        return $first?->utm_campaign;
    }

    public function attributeOrderToSource(Order $order): array
    {
        $identity = Identity::where('user_id', $order->user_id)
            ->orWhere('email_hash', $order->customer_email ? sha1($order->customer_email) : null)
            ->first();

        if (!$identity) {
            return [
                'uuid' => null,
                'source' => 'direct',
                'medium' => null,
                'campaign' => null,
                'type' => 'unattributed',
            ];
        }

        $attribution = $this->getAttribution($identity->uuid);

        return [
            'uuid' => $identity->uuid,
            'source' => $attribution['first_touch']['utm_source'] ?? 'direct',
            'medium' => $attribution['first_touch']['utm_medium'] ?? null,
            'campaign' => $attribution['first_touch']['utm_campaign'] ?? null,
            'type' => $attribution['first_touch'] ? 'first_touch' : 'unattributed',
            'click_ids' => $attribution['click_ids'],
            'referer' => $attribution['first_touch']['referer'] ?? null,
        ];
    }

    public function mergeIdentities(string $fromUuid, string $toUuid): bool
    {
        $fromCount = IdentityEvent::where('uuid', $fromUuid)->count();
        $toCount = IdentityEvent::where('uuid', $toUuid)->count();

        $keepUuid = $fromCount >= $toCount ? $fromUuid : $toUuid;
        $mergeUuid = $fromCount >= $toCount ? $toUuid : $fromUuid;

        try {
            DB::transaction(function () use ($mergeUuid, $keepUuid) {
                IdentityEvent::where('uuid', $mergeUuid)
                    ->update(['uuid' => $keepUuid]);

                Identity::where('uuid', $mergeUuid)->delete();
            });

            logger()->info('Identities merged', [
                'merged_uuid' => $mergeUuid,
                'kept_uuid' => $keepUuid,
            ]);

            return true;
        } catch (\Exception $e) {
            logger()->error('Identity merge failed', [
                'from' => $fromUuid,
                'to' => $toUuid,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function getTopSources(int $days = 30): array
    {
        return IdentityEvent::select(
            'utm_source',
            DB::raw('COUNT(DISTINCT uuid) as unique_visitors'),
            DB::raw('COUNT(*) as total_events'),
        )
            ->whereNotNull('utm_source')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('utm_source')
            ->orderByDesc('unique_visitors')
            ->get()
            ->toArray();
    }

    public function getCampaignPerformance(int $days = 30): array
    {
        return IdentityEvent::select(
            'utm_source',
            'utm_campaign',
            'utm_medium',
            DB::raw('COUNT(DISTINCT uuid) as unique_visitors'),
            DB::raw('COUNT(*) as total_events'),
        )
            ->whereNotNull('utm_campaign')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('utm_source', 'utm_campaign', 'utm_medium')
            ->orderByDesc('unique_visitors')
            ->get()
            ->toArray();
    }
}
