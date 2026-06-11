<?php

namespace App\Services;

use App\Models\AdAlert;
use App\Models\AdAutoPauseLog;
use App\Models\Meta\MetaCampaign;
use App\Models\Meta\MetaAdAccount;
use App\Services\Meta\FacebookGraphService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AdAutoPauseService
{
    private array $config;

    public function __construct(
        private FacebookGraphService $graph,
        private AdAccountHealthService $health,
        private AlertNotifier $notifier,
    ) {
        $this->config = config('tracking.auto_pause', []);
    }

    public function evaluateAndPause(): array
    {
        if (!($this->config['enabled'] ?? true)) {
            return ['skipped' => true, 'reason' => 'auto_pause_disabled'];
        }

        $results = [];
        $platforms = ['facebook', 'tiktok', 'google', 'snapchat', 'pinterest', 'twitter', 'linkedin'];

        foreach ($platforms as $platform) {
            $health = $this->health->getScore($platform);

            if ($health['status'] !== 'critical') {
                continue;
            }

            $campaigns = MetaCampaign::where('status', 'ACTIVE')->get();
            foreach ($campaigns as $campaign) {
                $result = $this->pauseCampaign($campaign, $platform, 'health_score', $health['score'], $this->config['cooldown_minutes'] ?? 120);
                if ($result) {
                    $results[] = $result;
                }
            }
        }

        return $results;
    }

    public function pauseForSpendAnomaly(MetaCampaign $campaign, string $platform, array $anomaly): ?array
    {
        return $this->pauseCampaign(
            $campaign, $platform, 'spend_anomaly',
            $anomaly['trigger_value'] ?? 0,
            $anomaly['threshold'] ?? 0,
        );
    }

    public function pauseForTrafficQuality(MetaCampaign $campaign, string $platform, array $qualityReport): ?array
    {
        return $this->pauseCampaign(
            $campaign, $platform, 'traffic_quality',
            $qualityReport['quality_score'] ?? 0,
            config('tracking.traffic_quality.quality_threshold_critical', 30),
        );
    }

    private function pauseCampaign(MetaCampaign $campaign, string $platform, string $triggerType, float $triggerValue, float $threshold): ?array
    {
        if (!$this->canPause($campaign, $platform)) {
            return null;
        }

        $account = $campaign->adAccount;
        if (!$account || !$account->access_token) {
            Log::warning("Cannot pause campaign {$campaign->id}: no ad account token");
            $this->logPauseAttempt($campaign, $platform, $triggerType, $triggerValue, $threshold, 'attempted', false, 'no_token');
            return null;
        }

        try {
            $this->graph->setUserAccessToken($account->access_token);
            $response = $this->graph->updateCampaignStatus($campaign->campaign_id, 'PAUSED');

            $campaign->update(['status' => 'PAUSED']);

            $this->logPauseAttempt($campaign, $platform, $triggerType, $triggerValue, $threshold, 'paused', true);

            AdAlert::create([
                'platform' => $platform,
                'type' => 'auto_pause',
                'severity' => 'critical',
                'title' => "تم إيقاف الحملة تلقائياً: {$campaign->name}",
                'body' => "السبب: {$triggerType} - القيمة: {$triggerValue} - الحد: {$threshold}",
                'data' => [
                    'trigger_type' => $triggerType,
                    'trigger_value' => $triggerValue,
                    'threshold' => $threshold,
                    'campaign_id' => $campaign->campaign_id,
                    'campaign_name' => $campaign->name,
                ],
                'campaign_id' => $campaign->campaign_id,
            ]);

            if ($this->config['notify_on_pause'] ?? true) {
                $this->notifier->send([
                    'channel' => 'all',
                    'type' => 'auto_pause',
                    'title' => "🚫 تم إيقاف حملة: {$campaign->name}",
                    'body' => "المنصة: {$platform}\nالسبب: {$triggerType}\nالقيمة: {$triggerValue}\nالحد: {$threshold}",
                    'severity' => 'critical',
                ]);
            }

            Cache::put(
                "auto_pause_cooldown:{$campaign->campaign_id}",
                true,
                now()->addMinutes($this->config['cooldown_minutes'] ?? 120),
            );

            return [
                'campaign_id' => $campaign->campaign_id,
                'campaign_name' => $campaign->name,
                'platform' => $platform,
                'action' => 'paused',
                'trigger_type' => $triggerType,
                'success' => true,
            ];
        } catch (\Exception $e) {
            Log::error("Auto-pause failed for campaign {$campaign->campaign_id}", [
                'error' => $e->getMessage(),
                'platform' => $platform,
                'trigger' => $triggerType,
            ]);

            $this->logPauseAttempt($campaign, $platform, $triggerType, $triggerValue, $threshold, 'attempted', false, $e->getMessage());

            return null;
        }
    }

    private function canPause(MetaCampaign $campaign, string $platform): bool
    {
        $cooldownKey = "auto_pause_cooldown:{$campaign->campaign_id}";
        if (Cache::has($cooldownKey)) {
            return false;
        }

        $maxDaily = $this->config['max_pauses_per_day'] ?? 5;
        $todayPauses = AdAutoPauseLog::where('campaign_id', $campaign->campaign_id)
            ->where('action', 'paused')
            ->whereDate('created_at', today())
            ->count();

        if ($todayPauses >= $maxDaily) {
            Log::warning("Max daily pauses reached for campaign {$campaign->campaign_id}", [
                'max' => $maxDaily,
                'today' => $todayPauses,
            ]);
            return false;
        }

        return true;
    }

    public function checkForResume(): array
    {
        if (!($this->config['auto_resume_enabled'] ?? false)) {
            return ['skipped' => true, 'reason' => 'auto_resume_disabled'];
        }

        $results = [];
        $pausedCampaigns = MetaCampaign::where('status', 'PAUSED')
            ->whereHas('adAccount', fn($q) => $q->whereNotNull('access_token'))
            ->get();

        foreach ($pausedCampaigns as $campaign) {
            $lastPause = AdAutoPauseLog::where('campaign_id', $campaign->campaign_id)
                ->where('action', 'paused')
                ->latest()
                ->first();

            if (!$lastPause || $lastPause->created_at->diffInHours(now()) < 1) {
                continue;
            }

            $health = $this->health->getScore('facebook');
            if ($health['score'] >= ($this->config['recovery_threshold'] ?? 70)) {
                $this->resumeCampaign($campaign, 'facebook', $health['score']);
                $results[] = ['campaign_id' => $campaign->campaign_id, 'action' => 'resumed'];
            }
        }

        return $results;
    }

    private function resumeCampaign(MetaCampaign $campaign, string $platform, float $healthScore): void
    {
        $account = $campaign->adAccount;
        if (!$account || !$account->access_token) {
            return;
        }

        try {
            $this->graph->setUserAccessToken($account->access_token);
            $this->graph->updateCampaignStatus($campaign->campaign_id, 'ACTIVE');
            $campaign->update(['status' => 'ACTIVE']);

            $this->logPauseAttempt($campaign, $platform, 'health_recovery', $healthScore, 70, 'resumed', true);

            AdAlert::create([
                'platform' => $platform,
                'type' => 'auto_resume',
                'severity' => 'info',
                'title' => "تم إعادة تشغيل الحملة: {$campaign->name}",
                'body' => "تحسن مؤشر الصحة إلى {$healthScore}",
                'data' => ['health_score' => $healthScore],
                'campaign_id' => $campaign->campaign_id,
            ]);

            if ($this->config['notify_on_pause'] ?? true) {
                $this->notifier->send([
                    'channel' => 'all',
                    'type' => 'auto_resume',
                    'title' => "✅ تم إعادة تشغيل حملة: {$campaign->name}",
                    'body' => "تحسن مؤشر الصحة إلى {$healthScore}",
                    'severity' => 'info',
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Auto-resume failed for campaign {$campaign->campaign_id}", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function logPauseAttempt(MetaCampaign $campaign, string $platform, string $triggerType, float $triggerValue, float $threshold, string $action, bool $success, ?string $error = null): void
    {
        try {
            AdAutoPauseLog::create([
                'platform' => $platform,
                'campaign_id' => $campaign->campaign_id,
                'campaign_name' => $campaign->name,
                'trigger_type' => $triggerType,
                'trigger_value' => $triggerValue,
                'threshold' => $threshold,
                'action' => $action,
                'success' => $success,
                'error_message' => $error,
                'context' => [
                    'campaign_status_before' => $campaign->getOriginal('status'),
                    'health_score' => $this->health->getScore($platform)['score'] ?? null,
                ],
            ]);
        } catch (\Exception $e) {
            Log::warning('Could not log auto-pause attempt', ['error' => $e->getMessage()]);
        }
    }
}
