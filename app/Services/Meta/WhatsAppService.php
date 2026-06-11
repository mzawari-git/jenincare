<?php

namespace App\Services\Meta;

use App\Models\MarketingSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private ?string $phoneNumberId;
    private ?string $accessToken;
    private ?string $businessAccountId;
    private const API_VERSION = 'v21.0';

    public function __construct()
    {
        $this->phoneNumberId = MarketingSetting::get('whatsapp_phone_number_id');
        $this->accessToken = MarketingSetting::get('whatsapp_access_token');
        $this->businessAccountId = MarketingSetting::get('whatsapp_business_account_id');
    }

    public function isEnabled(): bool
    {
        return !empty($this->phoneNumberId) && !empty($this->accessToken);
    }

    public function getApiUrl(): string
    {
        return "https://graph.facebook.com/" . self::API_VERSION . "/{$this->phoneNumberId}";
    }

    public function sendMessage(string $to, string $templateName, array $components = [], ?string $languageCode = 'ar'): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'message' => 'WhatsApp غير مكون'];
        }

        $to = $this->normalizePhone($to);

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $languageCode],
            ],
        ];

        if (!empty($components)) {
            $payload['template']['components'] = $components;
        }

        return $this->request('POST', '/messages', $payload);
    }

    public function sendText(string $to, string $text): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'message' => 'WhatsApp غير مكون'];
        }

        $to = $this->normalizePhone($to);

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => ['body' => $text],
        ];

        return $this->request('POST', '/messages', $payload);
    }

    public function sendInteractive(string $to, string $bodyText, array $buttons = []): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'message' => 'WhatsApp غير مكون'];
        }

        $to = $this->normalizePhone($to);

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'body' => ['text' => $bodyText],
                'action' => [
                    'buttons' => array_map(fn($btn) => [
                        'type' => 'reply',
                        'reply' => [
                            'id' => $btn['id'],
                            'title' => $btn['title'],
                        ],
                    ], array_slice($buttons, 0, 3)),
                ],
            ],
        ];

        return $this->request('POST', '/messages', $payload);
    }

    public function sendWelcomeMessage(string $to, string $customerName): array
    {
        $components = [
            [
                'type' => 'body',
                'parameters' => [
                    ['type' => 'text', 'text' => $customerName],
                ],
            ],
        ];

        return $this->sendMessage($to, 'welcome_message', $components);
    }

    public function sendBookingReminder(string $to, string $customerName, string $serviceName, string $date): array
    {
        $components = [
            [
                'type' => 'body',
                'parameters' => [
                    ['type' => 'text', 'text' => $customerName],
                    ['type' => 'text', 'text' => $serviceName],
                    ['type' => 'text', 'text' => $date],
                ],
            ],
        ];

        return $this->sendMessage($to, 'booking_reminder', $components);
    }

    public function sendFollowUp(string $to, string $customerName): array
    {
        $components = [
            [
                'type' => 'body',
                'parameters' => [
                    ['type' => 'text', 'text' => $customerName],
                ],
            ],
        ];

        return $this->sendMessage($to, 'follow_up', $components);
    }

    public function sendBulkMessages(array $phoneNumbers, string $templateName, array $variables = []): array
    {
        $results = ['sent' => 0, 'failed' => 0, 'errors' => []];

        foreach ($phoneNumbers as $to) {
            $components = [];
            if (!empty($variables[$to])) {
                $components = [
                    [
                        'type' => 'body',
                        'parameters' => array_map(fn($v) => ['type' => 'text', 'text' => $v], $variables[$to]),
                    ],
                ];
            }

            $result = $this->sendMessage($to, $templateName, $components);

            if ($result['success']) {
                $results['sent']++;
            } else {
                $results['failed']++;
                $results['errors'][] = ['phone' => $to, 'error' => $result['message'] ?? 'Unknown'];
            }

            usleep(100000);
        }

        return $results;
    }

    public function getPhoneNumberInfo(): ?array
    {
        if (!$this->isEnabled()) return null;

        try {
            $response = Http::withToken($this->accessToken)
                ->get("https://graph.facebook.com/" . self::API_VERSION . "/{$this->phoneNumberId}", [
                    'fields' => 'display_phone_number,verified_name,quality_rating,messaging_limit_tier',
                ]);

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error('WhatsApp phone info failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function getBusinessProfile(): ?array
    {
        if (!$this->businessAccountId) return null;

        try {
            $response = Http::withToken($this->accessToken)
                ->get("https://graph.facebook.com/" . self::API_VERSION . "/{$this->businessAccountId}", [
                    'fields' => 'name,vertical,id',
                ]);

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error('WhatsApp business profile failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function testConnection(): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'message' => 'WhatsApp غير مكون، يرجى إدخال Phone Number ID و Access Token'];
        }

        $info = $this->getPhoneNumberInfo();
        if ($info) {
            return [
                'success' => true,
                'message' => "تم الاتصال بنجاح - {$info['display_phone_number']}",
                'info' => $info,
            ];
        }

        return ['success' => false, 'message' => 'فشل الاتصال بـ WhatsApp'];
    }

    private function request(string $method, string $endpoint, ?array $data = null): array
    {
        try {
            $start = microtime(true);
            $response = Http::timeout(15)
                ->withToken($this->accessToken)
                ->{strtolower($method)}($this->getApiUrl() . $endpoint, $data);
            $duration = round((microtime(true) - $start) * 1000);

            $body = $response->json();
            $success = $response->successful();

            if (!$success) {
                Log::warning('WhatsApp API error', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'error' => $body['error']['message'] ?? 'Unknown',
                    'duration_ms' => $duration,
                ]);
            }

            return [
                'success' => $success,
                'message' => $success ? 'تم الإرسال بنجاح' : ($body['error']['message'] ?? 'خطأ غير معروف'),
                'data' => $body,
                'duration_ms' => $duration,
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp API exception', ['endpoint' => $endpoint, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'خطأ في الاتصال: ' . $e->getMessage()];
        }
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '970')) return $phone;
        if (str_starts_with($phone, '0')) return '970' . substr($phone, 1);
        if (strlen($phone) === 9) return '970' . $phone;

        return $phone;
    }
}
