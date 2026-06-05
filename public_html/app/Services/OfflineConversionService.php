<?php

namespace App\Services;

use App\Models\PosSale;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OfflineConversionService
{
    public function matchCustomer(PosSale $sale): ?string
    {
        $identity = null;

        if ($sale->customer_email) {
            $identity = \App\Models\Identity::where('email_hash', sha1($sale->customer_email))->first();
        }

        if (!$identity && $sale->customer_phone) {
            $identity = \App\Models\Identity::where('phone_hash', sha1($sale->customer_phone))->first();
        }

        if ($identity) {
            $sale->update([
                'uuid' => $identity->uuid,
                'user_id' => $identity->user_id,
                'matched_to_online' => true,
            ]);
        }

        return $identity?->uuid;
    }

    public function sendToMetaOffline(PosSale $sale): bool
    {
        $tracking = app(AdvertisingTrackingService::class);
        $pixelId = $tracking->getFbPixelId();
        $token = $tracking->getFbAccessToken();

        if (empty($pixelId) || empty($token)) {
            return false;
        }

        try {
            $response = Http::post("https://graph.facebook.com/v22.0/{$pixelId}/events", [
                'access_token' => $token,
                'data' => [[
                    'event_name' => 'Purchase',
                    'event_time' => $sale->sale_at->timestamp,
                    'event_id' => 'pos_' . $sale->pos_sale_id,
                    'user_data' => [
                        'em' => $sale->customer_email ? hash('sha256', $sale->customer_email) : null,
                        'ph' => $sale->customer_phone ? hash('sha256', $sale->customer_phone) : null,
                    ],
                    'custom_data' => [
                        'value' => (float) $sale->order_total,
                        'currency' => $sale->currency,
                        'source' => 'pos',
                    ],
                    'action_source' => 'physical_store',
                ]],
            ]);

            if ($response->successful()) {
                Log::info('Meta offline conversion sent', ['pos_sale_id' => $sale->pos_sale_id]);
                return true;
            }

            Log::warning('Meta offline conversion failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('Meta offline conversion exception', ['error' => $e->getMessage()]);
        }

        return false;
    }

    public function sendToTikTokOffline(PosSale $sale): bool
    {
        $tracking = app(AdvertisingTrackingService::class);
        $pixelId = $tracking->getTtPixelId();
        $token = $tracking->getTtAccessToken();

        if (empty($pixelId) || empty($token)) {
            return false;
        }

        try {
            $response = Http::post('https://analytics.tiktok.com/api/v2/offline/events', [
                'pixel_code' => $pixelId,
                'access_token' => $token,
                'event' => 'Purchase',
                'event_id' => 'pos_' . $sale->pos_sale_id,
                'timestamp' => $sale->sale_at->toIso8601String(),
                'context' => [
                    'user' => [
                        'email' => $sale->customer_email ? hash('sha256', $sale->customer_email) : null,
                        'phone' => $sale->customer_phone ? hash('sha256', $sale->customer_phone) : null,
                    ],
                ],
                'properties' => [
                    'value' => (float) $sale->order_total,
                    'currency' => $sale->currency,
                ],
            ]);

            if ($response->successful()) {
                Log::info('TikTok offline conversion sent', ['pos_sale_id' => $sale->pos_sale_id]);
                return true;
            }

            Log::warning('TikTok offline conversion failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('TikTok offline conversion exception', ['error' => $e->getMessage()]);
        }

        return false;
    }
}
