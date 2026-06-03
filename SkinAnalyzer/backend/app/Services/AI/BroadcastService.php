<?php
namespace App\Services\AI;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class BroadcastService
{
    public function sendToUser(int $userId, string $event, array $data): void
    {
        $user = User::find($userId);
        if (!$user || !$user->device_id) {
            Log::warning("BroadcastService: Cannot send to user {$userId} — no device registered.");
            return;
        }
        // Push notification via FCM
        if (class_exists(\App\Services\PushNotificationService::class)) {
            app(\App\Services\PushNotificationService::class)->send($user->device_id, $event, $data);
        }
        Log::info("BroadcastService: Sent '{$event}' to user {$userId}", $data);
    }
}
