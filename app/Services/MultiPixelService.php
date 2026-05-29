<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class MultiPixelService
{
    public function getPixelIds(string $platform): array
    {
        $setting = app(\App\Models\Setting::class);

        $pixelIds = [];

        $primaryKey = "{$platform}_pixel_id";
        $primary = $setting->where('key', $primaryKey)->value('value');
        if ($primary) {
            $pixelIds[] = $primary;
        }

        $backupKey = "{$platform}_backup_pixel_ids";
        $backupJson = $setting->where('key', $backupKey)->value('value');
        if ($backupJson) {
            $backup = json_decode($backupJson, true);
            if (is_array($backup)) {
                $pixelIds = array_merge($pixelIds, $backup);
            }
        }

        return array_unique(array_filter($pixelIds));
    }

    public function getTokens(string $platform): array
    {
        $setting = app(\App\Models\Setting::class);
        $tokens = [];

        $primaryKey = "{$platform}_access_token";
        $primary = $setting->where('key', $primaryKey)->value('value');
        if ($primary) {
            $tokens[] = $primary;
        }

        $backupKey = "{$platform}_backup_access_tokens";
        $backupJson = $setting->where('key', $backupKey)->value('value');
        if ($backupJson) {
            $backup = json_decode($backupJson, true);
            if (is_array($backup)) {
                $tokens = array_merge($tokens, $backup);
            }
        }

        return array_unique(array_filter($tokens));
    }

    public function fanOut(string $platform, array $events, callable $sendFn): array
    {
        $pixelIds = $this->getPixelIds($platform);
        $results = [];

        foreach ($pixelIds as $index => $pixelId) {
            try {
                $result = $sendFn($pixelId, $events, $index);
                $results[$pixelId] = $result;

                Log::info("Multi-pixel fan-out sent to {$platform}:{$pixelId}", [
                    'platform' => $platform,
                    'pixel_index' => $index,
                    'success' => $result['success'] ?? false,
                ]);
            } catch (\Exception $e) {
                Log::error("Multi-pixel fan-out failed for {$platform}:{$pixelId}", [
                    'error' => $e->getMessage(),
                ]);
                $results[$pixelId] = ['success' => false, 'error' => $e->getMessage()];
            }
        }

        return $results;
    }

    public function getStatus(string $platform): array
    {
        $pixelIds = $this->getPixelIds($platform);
        $status = [];

        foreach ($pixelIds as $pixelId) {
            $status[$pixelId] = [
                'pixel_id' => $pixelId,
                'is_primary' => $pixelId === ($pixelIds[0] ?? null),
                'last_error' => null,
                'healthy' => true,
            ];
        }

        return $status;
    }
}
