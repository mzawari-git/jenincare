<?php

namespace App\Notifications;

use App\Models\Device;
use App\Models\SkinScan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScanCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    protected SkinScan $scan;

    public function __construct(SkinScan $scan)
    {
        $this->scan = $scan;
    }

    public function via($notifiable): array
    {
        return ['fcm'];
    }

    public function toFcm($notifiable): array
    {
        $devices = Device::where('user_id', $notifiable->id)
            ->whereNotNull('fcm_token')
            ->get();

        $analysisData = $this->scan->analysis_data ?? [];
        $score = $analysisData['overall_health_score'] ?? $this->scan->overall_health_score;

        foreach ($devices as $device) {
            try {
                $this->sendPushNotification($device->fcm_token, [
                    'scan_id' => $this->scan->id,
                    'status' => 'completed',
                    'analysis_status' => $this->scan->analysis_status,
                    'overall_health_score' => (int) $score,
                    'message_ar' => 'اكتمل تحليل البشرة بنجاح',
                    'message_en' => 'Skin analysis completed successfully',
                ]);
            } catch (\Throwable $e) {
                Log::error('FCM send failed', [
                    'device_id' => $device->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'scan_id' => $this->scan->id,
            'sent' => $devices->count(),
        ];
    }

    protected function sendPushNotification(string $token, array $data): void
    {
        $serverKey = config('services.fcm.server_key');
        if (empty($serverKey)) {
            Log::warning('FCM server key not configured');
            return;
        }

        Http::withToken($serverKey)
            ->timeout(10)
            ->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $token,
                'priority' => 'high',
                'notification' => [
                    'title' => $data['message_en'],
                    'title_ar' => $data['message_ar'],
                    'body' => 'Score: ' . ($data['overall_health_score'] ?? '--') . '/100',
                    'sound' => 'default',
                ],
                'data' => $data,
            ]);
    }

    public function viaType(): string
    {
        return 'fcm';
    }
}
