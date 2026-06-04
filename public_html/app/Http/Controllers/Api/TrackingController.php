<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TrackingEventRequest;
use App\Services\AdvertisingTrackingService;
use Illuminate\Http\JsonResponse;

class TrackingController extends Controller
{
    private AdvertisingTrackingService $tracking;

    public function __construct(AdvertisingTrackingService $tracking)
    {
        $this->tracking = $tracking;
    }

    public function track(TrackingEventRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (!$this->tracking->isEnabled()) {
            return response()->json(['error' => 'Tracking is disabled'], 503);
        }

        $eventId = $this->tracking->trackEvent(
            eventName: $data['event_name'],
            customData: $data['custom_data'] ?? [],
            userData: $data['user_data'] ?? [],
            source: $data['source'] ?? 'custom_api',
            platforms: $data['platforms'] ?? null,
            eventId: $data['event_id'] ?? null,
        );

        if (!$eventId) {
            return response()->json([
                'status' => 'duplicate',
                'message' => 'Event was blocked by deduplication',
            ], 200);
        }

        return response()->json([
            'status' => 'ok',
            'event_id' => $eventId,
        ], 201);
    }

    public function batch(TrackingEventRequest $request): JsonResponse
    {
        $events = $request->input('events', []);

        if (empty($events)) {
            return response()->json(['error' => 'events array is required'], 422);
        }

        $results = [];
        foreach ($events as $i => $event) {
            $validated = validator($event, (new TrackingEventRequest())->rules())->validate();

            $eventId = $this->tracking->trackEvent(
                eventName: $validated['event_name'],
                customData: $validated['custom_data'] ?? [],
                userData: $validated['user_data'] ?? [],
                source: $validated['source'] ?? 'custom_api',
                platforms: $validated['platforms'] ?? null,
                eventId: $validated['event_id'] ?? null,
            );

            $results[] = [
                'index' => $i,
                'status' => $eventId ? 'ok' : 'duplicate',
                'event_id' => $eventId,
            ];
        }

        return response()->json([
            'status' => 'ok',
            'processed' => count(array_filter($results, fn($r) => $r['status'] === 'ok')),
            'duplicates' => count(array_filter($results, fn($r) => $r['status'] === 'duplicate')),
            'results' => $results,
        ]);
    }

    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'tracking_enabled' => $this->tracking->isEnabled(),
            'test_mode' => $this->tracking->isTestMode(),
            'time' => now()->toIso8601String(),
        ]);
    }
}
