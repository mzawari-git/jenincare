<?php

namespace Modules\Meta\Services;

use App\Models\MarketingSetting;
use App\Models\Meta\MetaAdAccount;
use App\Services\Meta\FacebookGraphService;
use Illuminate\Support\Facades\Log;

class LeadSyncService
{
    private FacebookGraphService $graph;

    public function __construct(FacebookGraphService $graph)
    {
        $this->graph = $graph;
    }

    public function syncLeadsFromFacebook(?string $accessToken = null): array
    {
        $token = $accessToken ?? $this->getAccessToken();
        if (!$token) {
            return ['success' => false, 'message' => 'لا يوجد رمز وصول صالح لـ Facebook'];
        }

        $this->graph->setUserAccessToken($token);
        $syncedCount = 0;
        $errors = [];

        try {
            $pages = $this->getPagesWithToken($token);

            if (empty($pages)) {
                return ['success' => false, 'message' => 'لم يتم العثور على صفحات مرتبطة بالحساب'];
            }

            foreach ($pages as $page) {
                $pageId = $page['id'] ?? null;
                $pageToken = $page['access_token'] ?? $token;

                if (!$pageId) continue;

                try {
                    $result = $this->syncLeadsFromPage($pageId, $pageToken);
                    $syncedCount += $result['synced'] ?? 0;
                } catch (\Exception $e) {
                    $errors[] = "صفحة {$pageId}: " . $e->getMessage();
                    Log::warning("Lead sync failed for page {$pageId}", ['error' => $e->getMessage()]);
                }
            }

            MarketingSetting::set('meta_leads_last_sync', now()->toIso8601String());
            MarketingSetting::set('meta_leads_total_synced', $syncedCount);

            return [
                'success' => true,
                'synced' => $syncedCount,
                'errors' => $errors,
                'message' => "تم مزامنة {$syncedCount} عميل محتمل",
            ];
        } catch (\Exception $e) {
            Log::error('Lead sync failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'خطأ في المزامنة: ' . $e->getMessage()];
        }
    }

    private function syncLeadsFromPage(string $pageId, string $pageToken): array
    {
        $this->graph->setUserAccessToken($pageToken);
        $synced = 0;

        $forms = $this->graph->get("{$pageId}/leadgen_forms", [
            'fields' => 'id,name,status',
            'limit' => 50,
        ]);

        $forms = $forms['data'] ?? [];

        foreach ($forms as $form) {
            if (($form['status'] ?? 'ACTIVE') !== 'ACTIVE') continue;

            $formId = $form['id'];
            $leads = $this->graph->get("{$formId}/leads", [
                'fields' => 'id,created_time,form_id,field_data,ad_id,ad_name,adset_id,adset_name,campaign_id,campaign_name',
                'limit' => 50,
            ]);

            $leads = $leads['data'] ?? [];

            foreach ($leads as $fbLead) {
                $exists = \Modules\Meta\Models\MetaLead::where('psid', $fbLead['id'] ?? '')
                    ->orWhere(function ($q) use ($fbLead) {
                        $fields = $this->parseFieldData($fbLead['field_data'] ?? []);
                        if (!empty($fields['email'])) {
                            $q->where('email', $fields['email']);
                        }
                    })
                    ->first();

                if ($exists) {
                    $exists->update([
                        'total_interactions' => $exists->total_interactions + 1,
                        'last_activity_at' => now(),
                        'meta_data' => array_merge($exists->meta_data ?? [], [
                            'last_fb_sync' => now()->toIso8601String(),
                        ]),
                    ]);
                    continue;
                }

                $fields = $this->parseFieldData($fbLead['field_data'] ?? []);

                $score = $this->calculateLeadScore($fields, $fbLead);

                \Modules\Meta\Models\MetaLead::create([
                    'psid' => $fbLead['id'] ?? null,
                    'sender_name' => $fields['full_name'] ?? $fields['name'] ?? null,
                    'email' => $fields['email'] ?? null,
                    'phone' => $fields['phone_number'] ?? $fields['phone'] ?? null,
                    'city' => $fields['city'] ?? null,
                    'country' => $fields['country'] ?? 'PS',
                    'gender' => $fields['gender'] ?? null,
                    'locale' => $fields['locale'] ?? null,
                    'source' => 'facebook',
                    'source_campaign' => $fbLead['campaign_name'] ?? null,
                    'engagement_type' => 'ad_click',
                    'lead_score' => $score,
                    'stage' => $this->getStageFromScore($score),
                    'intent' => $this->detectIntent($fields),
                    'purchase_probability' => min(1.0, $score / 100),
                    'total_interactions' => 1,
                    'last_activity_at' => now(),
                    'meta_data' => [
                        'form_id' => $fbLead['form_id'] ?? null,
                        'form_name' => $form['name'] ?? null,
                        'ad_id' => $fbLead['ad_id'] ?? null,
                        'ad_name' => $fbLead['ad_name'] ?? null,
                        'adset_id' => $fbLead['adset_id'] ?? null,
                        'adset_name' => $fbLead['adset_name'] ?? null,
                        'campaign_id' => $fbLead['campaign_id'] ?? null,
                        'campaign_name' => $fbLead['campaign_name'] ?? null,
                        'fb_created_time' => $fbLead['created_time'] ?? null,
                        'raw_field_data' => $fbLead['field_data'] ?? [],
                    ],
                    'custom_attributes' => $fields,
                ]);

                $synced++;
            }
        }

        return ['synced' => $synced];
    }

    private function parseFieldData(array $fieldData): array
    {
        $parsed = [];
        foreach ($fieldData as $field) {
            $name = $field['name'] ?? '';
            $values = $field['values'] ?? [];
            $value = !empty($values) ? reset($values) : null;

            match ($name) {
                'full_name' => $parsed['full_name'] = $value,
                'name' => $parsed['name'] = $value,
                'email' => $parsed['email'] = strtolower(trim($value)),
                'phone_number' => $parsed['phone_number'] = $value,
                'phone' => $parsed['phone'] = $value,
                'city' => $parsed['city'] = $value,
                'country' => $parsed['country'] = $value,
                'gender' => $parsed['gender'] = strtolower($value),
                'locale' => $parsed['locale'] = $value,
                'date_of_birth' => $parsed['date_of_birth'] = $value,
                'street_address' => $parsed['street_address'] = $value,
                'zip_code' => $parsed['zip_code'] = $value,
                'work_email' => $parsed['email'] = strtolower(trim($value)),
                'mobile_number' => $parsed['phone_number'] = $value,
                default => $parsed[$name] = $value,
            };
        }

        return $parsed;
    }

    public function calculateLeadScore(array $fields, array $fbLead = []): int
    {
        $score = 0;

        if (!empty($fields['email'])) $score += 20;
        if (!empty($fields['phone_number']) || !empty($fields['phone'])) $score += 25;
        if (!empty($fields['full_name']) || !empty($fields['name'])) $score += 10;
        if (!empty($fields['city'])) $score += 10;
        if (!empty($fields['date_of_birth'])) $score += 5;

        $createdAt = $fbLead['created_time'] ?? null;
        if ($createdAt) {
            $leadAge = now()->diffInHours(now()->parse($createdAt));
            if ($leadAge < 24) $score += 20;
            elseif ($leadAge < 72) $score += 10;
            elseif ($leadAge < 168) $score += 5;
        }

        if (!empty($fbLead['campaign_name'])) {
            $campaignName = strtolower($fbLead['campaign_name']);
            if (str_contains($campaignName, 'purchase') || str_contains($campaignName, 'convert')) {
                $score += 10;
            }
        }

        return min(100, $score);
    }

    public function getStageFromScore(int $score): string
    {
        return match (true) {
            $score >= 70 => 'hot',
            $score >= 40 => 'warm',
            $score >= 20 => 'engaged',
            default => 'new',
        };
    }

    public function detectIntent(array $fields): ?string
    {
        $allText = strtolower(implode(' ', array_filter($fields)));

        $intents = [
            'purchase' => ['اشتري', 'شراء', 'احجز', 'حجز', 'طلب', 'order', 'buy', 'book', 'purchase'],
            'trust' => ['ثقة', 'مراجعة', 'رأي', 'trust', 'review', 'opinion'],
            'awareness' => ['معلومات', 'تفاصيل', 'سعر', 'cost', 'price', 'info', 'detail'],
            'complaint' => ['شكوى', 'مشكلة', 'لا يعمل', 'complaint', 'problem', 'issue'],
        ];

        foreach ($intents as $intent => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($allText, $keyword)) {
                    return $intent;
                }
            }
        }

        return null;
    }

    private function getAccessToken(): ?string
    {
        $token = MarketingSetting::get('facebook_access_token');
        if ($token) return $token;

        $account = MetaAdAccount::where('is_active', true)
            ->whereNotNull('access_token')
            ->first();

        return $account?->access_token;
    }

    private function getPagesWithToken(string $token): array
    {
        $this->graph->setUserAccessToken($token);
        $result = $this->graph->get('me/accounts', [
            'fields' => 'id,name,access_token,category',
            'limit' => 50,
        ]);

        return $result['data'] ?? [];
    }

    public function getLeadStats(): array
    {
        $query = \Modules\Meta\Models\MetaLead::query();

        $total = $query->count();
        $today = $query->whereDate('created_at', today())->count();
        $thisWeek = $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $thisMonth = $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        $hot = $query->where('stage', 'hot')->count();
        $warm = $query->where('stage', 'warm')->count();
        $cold = $query->where('stage', 'cold')->count();
        $new = $query->where('stage', 'new')->count();

        $avgScore = $query->avg('lead_score') ?? 0;

        $topCampaigns = $query->whereNotNull('source_campaign')
            ->selectRaw('source_campaign, COUNT(*) as count, AVG(lead_score) as avg_score')
            ->groupBy('source_campaign')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return [
            'total' => $total,
            'today' => $today,
            'this_week' => $thisWeek,
            'this_month' => $thisMonth,
            'hot' => $hot,
            'warm' => $warm,
            'cold' => $cold,
            'new' => $new,
            'avg_score' => round($avgScore, 1),
            'top_campaigns' => $topCampaigns,
            'last_sync' => MarketingSetting::get('meta_leads_last_sync'),
        ];
    }
}
