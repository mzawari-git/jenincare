<?php

namespace App\Listeners;

use App\Events\ScanApproved;
use App\Notifications\ScanApprovedNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class NotifyUserScanApproved
{
    public function handle(ScanApproved $event): void
    {
        $scan = $event->scan;
        $user = $scan->user;

        if (! $user) {
            Log::warning('ScanApproved listener: No user found for scan.', [
                'scan_id' => $scan->id,
            ]);
            return;
        }

        $user->notify(new ScanApprovedNotification($scan));

        $this->sendWebhookNotification($scan, $user);
    }

    private function sendWebhookNotification($scan, $user): void
    {
        $webhookUrl = config('skinanalyzer.webhooks.scan_approved');

        if (empty($webhookUrl)) {
            return;
        }

        try {
            Http::timeout(10)
                ->post($webhookUrl, [
                    'event' => 'scan.approved',
                    'scan_id' => $scan->id,
                    'user_id' => $user->id,
                    'status' => $scan->status,
                    'overall_health_score' => $scan->overall_health_score,
                    'approved_at' => $scan->approved_at?->toIso8601String(),
                    'timestamp' => now()->toIso8601String(),
                ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send webhook for scan approval.', [
                'scan_id' => $scan->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
