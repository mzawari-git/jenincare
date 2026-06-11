<?php

namespace App\Services\Meta;

use App\Models\MarketingSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ConversationService
{
    private FacebookGraphService $graph;
    private ?string $pageId;
    private ?string $pageAccessToken;

    public function __construct(FacebookGraphService $graph)
    {
        $this->graph = $graph;
        $this->pageId = MarketingSetting::get('facebook_page_id');
        $this->pageAccessToken = MarketingSetting::get('facebook_page_access_token');
    }

    public function isEnabled(): bool
    {
        return !empty($this->pageId) && !empty($this->pageAccessToken);
    }

    public function getConversations(int $limit = 25, ?string $after = null): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'conversations' => [], 'paging' => null];
        }

        $this->graph->setUserAccessToken($this->pageAccessToken);

        $params = [
            'fields' => 'id,message_count,unread_count,updated_time,snippet,participants,can_reply',
            'limit' => $limit,
        ];

        if ($after) $params['after'] = $after;

        $result = $this->graph->get("{$this->pageId}/conversations", $params);

        $conversations = [];
        foreach ($result['data'] ?? [] as $conv) {
            $participants = collect($result['participants'] ?? $conv['participants'] ?? [])->pluck('name')->filter()->values();
            $conversations[] = [
                'id' => $conv['id'],
                'snippet' => $conv['snippet'] ?? '',
                'message_count' => $conv['message_count'] ?? 0,
                'unread_count' => $conv['unread_count'] ?? 0,
                'updated_time' => $conv['updated_time'] ?? '',
                'participants' => $participants->toArray(),
                'can_reply' => $conv['can_reply'] ?? true,
            ];
        }

        return [
            'success' => true,
            'conversations' => $conversations,
            'paging' => $result['paging'] ?? null,
        ];
    }

    public function getMessages(string $conversationId, int $limit = 25): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'messages' => []];
        }

        $this->graph->setUserAccessToken($this->pageAccessToken);

        $result = $this->graph->get("{$conversationId}/messages", [
            'fields' => 'id,from,to,message,created_time,attachments',
            'limit' => $limit,
        ]);

        $messages = [];
        foreach ($result['data'] ?? [] as $msg) {
            $from = $msg['from'] ?? [];
            $isPage = (string)($from['id'] ?? '') === (string)$this->pageId;

            $messages[] = [
                'id' => $msg['id'],
                'from_id' => $from['id'] ?? '',
                'from_name' => $from['name'] ?? '',
                'is_page' => $isPage,
                'message' => $msg['message'] ?? '',
                'created_time' => $msg['created_time'] ?? '',
                'attachments' => $msg['attachments']['data'] ?? [],
            ];
        }

        return [
            'success' => true,
            'messages' => array_reverse($messages),
        ];
    }

    public function sendMessage(string $recipientId, string $text): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'message' => 'Facebook Messaging غير مكون'];
        }

        try {
            $response = Http::withToken($this->pageAccessToken)
                ->post("https://graph.facebook.com/v22.0/{$this->pageId}/messages", [
                    'recipient' => ['id' => $recipientId],
                    'message' => ['text' => $text],
                    'messaging_type' => 'RESPONSE',
                ]);

            $body = $response->json();

            if ($response->successful()) {
                return ['success' => true, 'message_id' => $body['message_id'] ?? null];
            }

            Log::warning('Facebook message send failed', ['error' => $body]);
            return ['success' => false, 'message' => $body['error']['message'] ?? 'فشل الإرسال'];
        } catch (\Exception $e) {
            Log::error('Facebook message exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'خطأ: ' . $e->getMessage()];
        }
    }

    public function sendQuickReply(string $recipientId, string $text, array $quickReplies): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'message' => 'Facebook Messaging غير مكون'];
        }

        $replies = array_map(fn($qr) => [
            'content_type' => 'text',
            'title' => $qr['title'],
            'payload' => $qr['payload'] ?? $qr['title'],
        ], array_slice($quickReplies, 0, 11));

        try {
            $response = Http::withToken($this->pageAccessToken)
                ->post("https://graph.facebook.com/v22.0/{$this->pageId}/messages", [
                    'recipient' => ['id' => $recipientId],
                    'message' => [
                        'text' => $text,
                        'quick_replies' => $replies,
                    ],
                    'messaging_type' => 'RESPONSE',
                ]);

            return ['success' => $response->successful()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'خطأ: ' . $e->getMessage()];
        }
    }

    public function sendTemplate(string $recipientId, string $templateName, array $params = [], string $languageCode = 'ar'): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'message' => 'Facebook Messaging غير مكون'];
        }

        $components = [];
        if (!empty($params)) {
            $components[] = [
                'type' => 'body',
                'parameters' => array_map(fn($p) => ['type' => 'text', 'text' => $p], $params),
            ];
        }

        try {
            $response = Http::withToken($this->pageAccessToken)
                ->post("https://graph.facebook.com/v22.0/{$this->pageId}/messages", [
                    'recipient' => ['id' => $recipientId],
                    'message' => [
                        'template' => [
                            'name' => $templateName,
                            'language' => ['code' => $languageCode],
                            'components' => $components,
                        ],
                    ],
                    'messaging_type' => 'RESPONSE',
                ]);

            return ['success' => $response->successful()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'خطأ: ' . $e->getMessage()];
        }
    }

    public function getUnreadCount(): int
    {
        $result = $this->getConversations(100);
        $total = 0;
        foreach ($result['conversations'] ?? [] as $conv) {
            $total += $conv['unread_count'] ?? 0;
        }
        return $total;
    }

    public function getStats(): array
    {
        $conversations = $this->getConversations(100);
        $all = $conversations['conversations'] ?? [];

        $total = count($all);
        $unread = collect($all)->sum('unread_count');
        $today = collect($all)->filter(function ($c) {
            return strtotime($c['updated_time'] ?? '') >= strtotime('today');
        })->count();

        return [
            'total' => $total,
            'unread' => $unread,
            'today' => $today,
            'avg_response_time' => 'N/A',
        ];
    }
}
