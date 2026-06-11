<?php

namespace Modules\CustomAdmin\Http\Controllers;

use App\Models\AdAlert;
use App\Models\Meta\MetaCampaign;
use App\Services\AlertNotifier;
use App\Services\AdAutoPauseService;
use App\Services\AdAccountHealthService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class MetaWebhookController extends Controller
{
    public function __construct(
        private AlertNotifier $notifier,
        private AdAutoPauseService $autoPause,
        private AdAccountHealthService $health,
    ) {}

    public function verify(Request $request)
    {
        $mode = $request->input('hub_mode');
        $token = $request->input('hub_verify_token');
        $challenge = $request->input('hub_challenge');

        $expectedToken = config('meta.webhook_verify_token');

        if ($mode === 'subscribe' && $token === $expectedToken) {
            Log::info('Meta webhook verified', ['challenge' => $challenge]);
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        Log::warning('Meta webhook verification failed', [
            'mode' => $mode,
            'token_match' => $token === $expectedToken,
        ]);

        return response('Verification failed', 403);
    }

    public function receiveWebhook(Request $request)
    {
        $payload = $request->all();

        if (empty($payload)) {
            return response()->json(['ok' => true]);
        }

        Log::info('Meta webhook received', ['fields' => array_keys($payload)]);

        try {
            $this->processPayload($payload);
        } catch (\Exception $e) {
            Log::error('Meta webhook processing error', [
                'error' => $e->getMessage(),
                'payload_keys' => array_keys($payload),
            ]);
        }

        return response()->json(['ok' => true]);
    }

    private function processPayload(array $payload): void
    {
        $entry = $payload['entry'][0] ?? null;
        if (!$entry) {
            return;
        }

        $changes = $entry['changes'] ?? [];
        foreach ($changes as $change) {
            $this->processChange($change);
        }
    }

    private function processChange(array $change): void
    {
        $field = $change['field'] ?? '';
        $value = $change['value'] ?? [];

        match ($field) {
            'ad_campaign_status' => $this->handleCampaignStatus($value),
            'ad_status' => $this->handleAdStatus($value),
            'campaign_delivery' => $this->handleCampaignDelivery($value),
            'ad_review' => $this->handleAdReview($value),
            'ad_policy_violation' => $this->handlePolicyViolation($value),
            default => Log::debug('Meta webhook: unhandled field', ['field' => $field]),
        };
    }

    private function handleCampaignStatus(array $value): void
    {
        $campaignId = $value['campaign_id'] ?? '';
        $newStatus = $value['status'] ?? '';
        $adAccountId = $value['ad_account_id'] ?? '';

        if (empty($campaignId)) {
            return;
        }

        Log::info("Campaign {$campaignId} status changed to {$newStatus}");

        MetaCampaign::where('campaign_id', $campaignId)
            ->update(['status' => $newStatus]);
    }

    private function handleCampaignDelivery(array $value): void
    {
        $campaignId = $value['campaign_id'] ?? '';
        $deliveryStatus = $value['delivery_status'] ?? '';
        $reasons = $value['reasons'] ?? [];

        if (empty($campaignId)) {
            return;
        }

        $campaign = MetaCampaign::where('campaign_id', $campaignId)->first();
        if (!$campaign) {
            return;
        }

        $isIssue = in_array($deliveryStatus, ['limited', 'not_delivering', 'rejected', 'error']);

        $severity = $isIssue ? 'critical' : 'info';

        AdAlert::create([
            'platform' => 'facebook',
            'type' => 'campaign_delivery',
            'severity' => $severity,
            'title' => "تغيير حالة توصيل الحملة: {$campaign->name}",
            'body' => "حالة التوصيل: {$deliveryStatus}\nالأسباب: " . implode(', ', $reasons),
            'data' => [
                'delivery_status' => $deliveryStatus,
                'reasons' => $reasons,
                'campaign_id' => $campaignId,
            ],
            'campaign_id' => $campaignId,
        ]);

        if ($isIssue && $campaign->status === 'ACTIVE') {
            $this->autoPause->pauseForSpendAnomaly($campaign, 'facebook', [
                'trigger_value' => 0,
                'threshold' => 0,
            ]);
        }

        $this->notifier->send([
            'channel' => 'all',
            'type' => 'auto_pause',
            'platform' => 'facebook',
            'title' => $severity === 'critical' ? "🚫 مشكلة توصيل: {$campaign->name}" : "ℹ️ تحديث توصيل: {$campaign->name}",
            'body' => "الحالة: {$deliveryStatus}\nالأسباب: " . implode(', ', $reasons),
            'severity' => $severity,
        ]);
    }

    private function handleAdReview(array $value): void
    {
        $adId = $value['ad_id'] ?? '';
        $reviewStatus = $value['review_status'] ?? '';

        AdAlert::create([
            'platform' => 'facebook',
            'type' => 'ad_review',
            'severity' => $reviewStatus === 'rejected' ? 'critical' : 'info',
            'title' => "تحديث مراجعة الإعلان",
            'body' => "حالة المراجعة: {$reviewStatus}",
            'data' => ['ad_id' => $adId, 'review_status' => $reviewStatus],
        ]);
    }

    private function handlePolicyViolation(array $value): void
    {
        $adId = $value['ad_id'] ?? '';
        $issue = $value['issue'] ?? '';
        $summary = $value['summary'] ?? '';

        AdAlert::create([
            'platform' => 'facebook',
            'type' => 'policy_violation',
            'severity' => 'critical',
            'title' => '🚨 مخالفة سياسات الإعلانات',
            'body' => "المشكلة: {$issue}\nالتفاصيل: {$summary}",
            'data' => [
                'ad_id' => $adId,
                'issue' => $issue,
                'summary' => $summary,
            ],
        ]);

        $this->health->computeScore('facebook');

        $this->notifier->send([
            'channel' => 'all',
            'type' => 'health_critical',
            'platform' => 'facebook',
            'title' => "🚨 مخالفة سياسات: {$issue}",
            'body' => "تفاصيل: {$summary}",
            'severity' => 'critical',
        ]);
    }

    private function handleAdStatus(array $value): void
    {
        $adId = $value['ad_id'] ?? '';
        $newStatus = $value['status'] ?? '';

        Log::info("Ad {$adId} status changed to {$newStatus}");

        \App\Models\Meta\MetaAd::where('ad_id', $adId)
            ->update(['status' => $newStatus]);
    }
}
