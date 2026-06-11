<?php

namespace App\Services;

use App\Models\AdAlert;
use App\Models\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AlertNotifier
{
    private array $config;

    public function __construct()
    {
        $this->config = config('tracking.notifications', []);
    }

    public function send(array $params): void
    {
        $type = $params['type'] ?? 'general';
        $notifyOn = $this->config['notify_on'] ?? [];

        $shouldNotify = match ($type) {
            'health_critical' => $notifyOn['health_critical'] ?? true,
            'spend_anomaly' => $notifyOn['spend_anomaly'] ?? true,
            'auto_pause' => $notifyOn['auto_pause'] ?? true,
            'auto_resume' => $notifyOn['auto_resume'] ?? false,
            'traffic_drop' => $notifyOn['traffic_drop'] ?? true,
            'webhook_received' => $notifyOn['webhook_received'] ?? true,
            default => true,
        };

        if (!$shouldNotify) {
            return;
        }

        $title = $params['title'] ?? 'تنبيه إعلانات';
        $body = $params['body'] ?? '';
        $severity = $params['severity'] ?? 'info';
        $platform = $params['platform'] ?? 'general';

        $this->createAdminNotification($title, $body, $severity, $type, $platform);

        $this->sendSlack($title, $body, $severity);
        $this->sendTelegram($title, $body, $severity);
    }

    public function createAdminNotification(string $title, string $body, string $severity, string $type, string $platform = 'general', ?array $extraData = null): void
    {
        try {
            $users = \App\Models\User::where('role', 'admin')->get();

            foreach ($users as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => "ad_alert:{$type}",
                    'title' => $title,
                    'body' => $body,
                    'data' => array_merge([
                        'severity' => $severity,
                        'platform' => $platform,
                        'alert_type' => $type,
                    ], $extraData ?? []),
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Could not create admin notification', ['error' => $e->getMessage()]);
        }
    }

    public function sendSlack(string $title, string $body, string $severity): void
    {
        $webhook = $this->config['slack_webhook'] ?? '';
        if (empty($webhook)) {
            return;
        }

        $color = match ($severity) {
            'critical' => '#FF0000',
            'warning' => '#FFA500',
            default => '#36a64f',
        };

        try {
            Http::post($webhook, [
                'attachments' => [[
                    'color' => $color,
                    'title' => $title,
                    'text' => $body,
                    'footer' => 'Samah Care Ad Monitor',
                    'ts' => time(),
                ]],
            ]);
        } catch (\Exception $e) {
            Log::warning('Slack notification failed', ['error' => $e->getMessage()]);
        }
    }

    public function sendTelegram(string $title, string $body, string $severity): void
    {
        $token = $this->config['telegram_bot_token'] ?? '';
        $chatId = $this->config['telegram_chat_id'] ?? '';

        if (empty($token) || empty($chatId)) {
            return;
        }

        $emoji = match ($severity) {
            'critical' => "🔴",
            'warning' => "🟡",
            default => "🟢",
        };

        $message = "{$emoji} *{$title}*\n\n{$body}";

        try {
            Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);
        } catch (\Exception $e) {
            Log::warning('Telegram notification failed', ['error' => $e->getMessage()]);
        }
    }
}
