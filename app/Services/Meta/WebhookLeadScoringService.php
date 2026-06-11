<?php

namespace App\Services\Meta;

use App\Services\AI\AISanitizerService;
use Illuminate\Support\Facades\Log;

class WebhookLeadScoringService
{
    private AbTestService $abTestService;
    private EnhancedMatchingService $matchingService;

    public function __construct(
        AbTestService $abTestService,
        EnhancedMatchingService $matchingService,
    ) {
        $this->abTestService = $abTestService;
        $this->matchingService = $matchingService;
    }

    public function processLeadEvent(array $webhookData): array
    {
        $leadId = $webhookData['lead_id'] ?? null;
        $formId = $webhookData['form_id'] ?? null;
        $adId = $webhookData['ad_id'] ?? null;

        if (!$leadId) {
            return ['success' => false, 'message' => 'No lead_id in webhook'];
        }

        $leadData = $this->fetchLeadFromFacebook($leadId);
        if (!$leadData) {
            return ['success' => false, 'message' => 'Could not fetch lead data'];
        }

        $score = $this->calculateRealtimeScore($leadData, $webhookData);

        $lead = \Modules\Meta\Models\MetaLead::where('psid', $leadId)->first();

        if ($lead) {
            $lead->update([
                'lead_score' => max($lead->lead_score, $score),
                'stage' => $this->getStageFromScore(max($lead->lead_score, $score)),
                'last_activity_at' => now(),
            ]);
        }

        $this->sendScoreNotification($leadData, $score);

        return [
            'success' => true,
            'lead_id' => $leadId,
            'score' => $score,
            'stage' => $this->getStageFromScore($score),
        ];
    }

    public function processPageEvent(array $webhookData): array
    {
        $event = $webhookData['event'] ?? null;
        $senderId = $webhookData['sender']['id'] ?? null;

        if (!$senderId) return ['success' => false];

        $psidToScore = $this->getEventScore($event);

        $lead = \Modules\Meta\Models\MetaLead::where('psid', $senderId)->first();
        if ($lead) {
            $newScore = min(100, $lead->lead_score + $psidToScore);
            $lead->update([
                'lead_score' => $newScore,
                'total_interactions' => $lead->total_interactions + 1,
                'last_activity_at' => now(),
                'stage' => $this->getStageFromScore($newScore),
            ]);

            $this->trackAbTestImpression($lead);
        }

        return ['success' => true, 'score_delta' => $psidToScore];
    }

    private function fetchLeadFromFacebook(string $leadId): ?array
    {
        try {
            $service = app(ConversationService::class);
            $token = \App\Models\MarketingSetting::get('facebook_page_access_token');

            if (!$token) return null;

            $response = \Illuminate\Support\Facades\Http::withToken($token)
                ->get("https://graph.facebook.com/v22.0/{$leadId}", [
                    'fields' => 'id,created_time,form_id,field_data,ad_id,ad_name,adset_id,campaign_id',
                ]);

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error('Failed to fetch lead from Facebook', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function calculateRealtimeScore(array $leadData, array $webhookData): int
    {
        $score = 0;

        $fieldData = $leadData['field_data'] ?? [];
        $parsed = $this->parseFieldData($fieldData);

        if (!empty($parsed['email'])) $score += 20;
        if (!empty($parsed['phone_number'])) $score += 25;
        if (!empty($parsed['full_name'])) $score += 10;
        if (!empty($parsed['city'])) $score += 10;
        if (!empty($parsed['date_of_birth'])) $score += 5;

        $createdAt = $leadData['created_time'] ?? null;
        if ($createdAt) {
            $ageHours = now()->diffInHours(now()->parse($createdAt));
            if ($ageHours < 1) $score += 25;
            elseif ($ageHours < 24) $score += 15;
            elseif ($ageHours < 72) $score += 5;
        }

        $adName = strtolower($leadData['ad_name'] ?? '');
        if (str_contains($adName, 'convert') || str_contains($adName, 'purchase')) $score += 10;

        $formName = strtolower($leadData['form_id'] ?? '');
        if (strlen($formName) > 0) $score += 5;

        return min(100, $score);
    }

    private function getEventScore(string $event): int
    {
        return match ($event) {
            'message', 'messaging_postbacks' => 5,
            'page_engagement' => 3,
            'like' => 2,
            'comment' => 8,
            'share' => 10,
            'link_click' => 5,
            default => 1,
        };
    }

    private function parseFieldData(array $fieldData): array
    {
        $parsed = [];
        foreach ($fieldData as $field) {
            $name = $field['name'] ?? '';
            $values = $field['values'] ?? [];
            $value = !empty($values) ? reset($values) : null;
            $parsed[$name] = $value;
        }
        return $parsed;
    }

    private function getStageFromScore(int $score): string
    {
        return match (true) {
            $score >= 70 => 'hot',
            $score >= 40 => 'warm',
            $score >= 20 => 'engaged',
            default => 'new',
        };
    }

    private function sendScoreNotification(array $leadData, int $score): void
    {
        if ($score >= 70) {
            $name = 'عميل دافئ';
            $color = 'danger';
        } elseif ($score >= 40) {
            $name = 'عميل متوسط';
            $color = 'warning';
        } else {
            return;
        }

        $parsed = $this->parseFieldData($leadData['field_data'] ?? []);
        $leadName = $parsed['full_name'] ?? 'غير معروف';
        $adName = $leadData['ad_name'] ?? '-';

        \App\Models\Notification::create([
            'title' => "عميل محتمل {$name}: {$leadName}",
            'message' => "التقييم: {$score}/100 - الحملة: {$adName}",
            'type' => 'lead',
            'data' => ['lead_score' => $score],
        ]);
    }

    private function trackAbTestImpression($lead): void
    {
        $adId = $lead->ad_id ?? null;
        if (!$adId) return;

        $activeTests = $this->abTestService->getActiveTests();
        foreach ($activeTests as $test) {
            if ($test->campaign_id && $test->variant_a_id) {
                $this->abTestService->recordImpression($test->id, 'a');
            }
        }
    }
}
