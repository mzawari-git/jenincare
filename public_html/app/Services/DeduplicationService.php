<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class DeduplicationService
{
    private int $defaultWindow;
    private array $windows;

    private const DEFAULT_WINDOW_SECONDS = 300;
    private const MAX_WINDOW_SECONDS = 86400;
    private const KEY_PREFIX = 'dedup_';

    public function __construct()
    {
        $this->defaultWindow = (int) config('tracking.dedup_window', self::DEFAULT_WINDOW_SECONDS);
        $this->windows = config('tracking.dedup_windows', [
            'ViewContent' => 60,
            'AddToCart' => 120,
            'InitiateCheckout' => 300,
            'Purchase' => 86400,
            'Lead' => 86400,
            'Subscribe' => 86400,
            'Search' => 30,
            'Contact' => 86400,
        ]);
    }

    public function isDuplicate(string $eventName, array $identifiers): bool
    {
        $key = $this->buildKey($eventName, $identifiers);
        $window = $this->getWindow($eventName);

        if (Redis::exists($key)) {
            Log::debug('DeduplicationService: Duplicate event blocked', [
                'key' => $key,
                'event' => $eventName,
            ]);
            return true;
        }

        Redis::setex($key, $window, '1');
        return false;
    }

    public function markProcessed(string $eventName, array $identifiers, ?int $ttl = null): void
    {
        $key = $this->buildKey($eventName, $identifiers);
        $window = $ttl ?? $this->getWindow($eventName);
        Redis::setex($key, $window, '1');
    }

    public function clearDedup(string $eventName, array $identifiers): void
    {
        $key = $this->buildKey($eventName, $identifiers);
        Redis::del($key);
    }

    public function checkAndMark(string $eventName, string $eventId, ?string $key2 = null, ?string $key3 = null): bool
    {
        $window = $this->getWindowForEvent($eventName);

        $primaryKey = self::KEY_PREFIX . $eventName . '_' . $eventId;
        if (Redis::exists($primaryKey)) {
            Log::debug('Deduplication: primary key duplicate', [
                'event' => $eventName, 'event_id' => $eventId, 'key' => $primaryKey,
            ]);
            return false;
        }

        if ($key2 !== null) {
            $key2Cache = self::KEY_PREFIX . $eventName . '_' . $key2;
            if (Redis::exists($key2Cache)) {
                Log::debug('Deduplication: secondary key duplicate', [
                    'event' => $eventName, 'event_id' => $eventId, 'key' => $key2Cache,
                ]);
                return false;
            }
        }

        if ($key3 !== null) {
            $key3Cache = self::KEY_PREFIX . $eventName . '_' . $key3;
            if (Redis::exists($key3Cache)) {
                Log::debug('Deduplication: tertiary key duplicate', [
                    'event' => $eventName, 'event_id' => $eventId, 'key' => $key3Cache,
                ]);
                return false;
            }
        }

        Redis::setex($primaryKey, (int) $window, '1');

        if ($key2 !== null) {
            Redis::setex($key2Cache, (int) $window, '1');
        }
        if ($key3 !== null) {
            Redis::setex($key3Cache, (int) $window, '1');
        }

        return true;
    }

    public function isDuplicateEvent(string $eventName, string $eventId): bool
    {
        $key = self::KEY_PREFIX . $eventName . '_' . $eventId;
        return (bool) Redis::exists($key);
    }

    public function flush(string $eventName = null): void
    {
        if ($eventName) {
            $pattern = self::KEY_PREFIX . $eventName . '*';
            $keys = Redis::keys($pattern);
            if (!empty($keys)) {
                Redis::del($keys);
            }
        }
    }

    private function getWindowForEvent(string $eventName): int
    {
        $window = $this->windows[$eventName] ?? $this->defaultWindow;
        return min($window, self::MAX_WINDOW_SECONDS);
    }

    private function buildKey(string $eventName, array $identifiers): string
    {
        ksort($identifiers);
        return self::KEY_PREFIX . $eventName . '_' . md5(json_encode($identifiers));
    }

    private function getWindow(string $eventName): int
    {
        return min(
            $this->windows[$eventName] ?? $this->defaultWindow,
            self::MAX_WINDOW_SECONDS
        );
    }

    public function getStats(): array
    {
        return [
            'default_window' => $this->defaultWindow,
            'event_windows' => $this->windows,
        ];
    }
}
