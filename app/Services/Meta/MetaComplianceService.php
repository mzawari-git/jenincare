<?php

namespace App\Services\Meta;

use App\Models\Meta\MetaComplianceLog;
use App\Models\Meta\MetaSpendingLimit;
use App\Models\Meta\MetaAdAccount;
use App\Models\Meta\MetaCampaign;
use App\Services\Meta\FacebookGraphService;
use Illuminate\Support\Facades\Log;

class MetaComplianceService
{
    protected $graphService;

    public function __construct(FacebookGraphService $graphService)
    {
        $this->graphService = $graphService;
    }

    public function checkAccountHealth($accountId)
    {
        $account = MetaAdAccount::findOrFail($accountId);
        
        $this->graphService->setAccessToken($account->access_token);

        $health = [
            'account_status' => $account->account_status,
            'policy_violations' => $this->getPolicyViolations($accountId),
            'rejected_ads' => $this->getRejectedAds($accountId),
            'spending_limits' => $this->getSpendingLimitsStatus($accountId),
            'delivery_issues' => $this->getDeliveryIssues($accountId),
            'overall_score' => 0,
        ];

        $health['overall_score'] = $this->calculateHealthScore($health);

        return $health;
    }

    protected function getPolicyViolations($accountId)
    {
        return MetaComplianceLog::where('ad_account_id', $accountId)
            ->where('type', 'policy_violation')
            ->where('status', 'open')
            ->count();
    }

    protected function getRejectedAds($accountId)
    {
        return MetaComplianceLog::where('ad_account_id', $accountId)
            ->where('type', 'rejection')
            ->where('status', 'open')
            ->count();
    }

    protected function getSpendingLimitsStatus($accountId)
    {
        $limits = MetaSpendingLimit::where('ad_account_id', $accountId)
            ->where('status', 'active')
            ->get();

        $triggered = $limits->where('status', 'triggered')->count();
        $nearLimit = $limits->filter(function ($limit) {
            return $limit->daily_limit && 
                   ($limit->current_spend / $limit->daily_limit) >= ($limit->alert_threshold / 100);
        })->count();

        return [
            'total' => $limits->count(),
            'triggered' => $triggered,
            'near_limit' => $nearLimit,
        ];
    }

    protected function getDeliveryIssues($accountId)
    {
        return MetaComplianceLog::where('ad_account_id', $accountId)
            ->where('type', 'delivery_issue')
            ->where('status', 'open')
            ->count();
    }

    protected function calculateHealthScore($health)
    {
        $score = 100;

        $score -= $health['policy_violations'] * 20;
        $score -= $health['rejected_ads'] * 10;
        $score -= $health['spending_limits']['triggered'] * 15;
        $score -= $health['delivery_issues'] * 5;

        return max(0, min(100, $score));
    }

    public function logPolicyViolation($accountId, $campaignId, $adId, $policyName, $description, $severity = 'medium')
    {
        return MetaComplianceLog::create([
            'ad_account_id' => $accountId,
            'campaign_id' => $campaignId,
            'ad_id' => $adId,
            'type' => 'policy_violation',
            'severity' => $severity,
            'policy_name' => $policyName,
            'description' => $description,
            'status' => 'open',
        ]);
    }

    public function logAdRejection($accountId, $campaignId, $adId, $reason, $severity = 'high')
    {
        return MetaComplianceLog::create([
            'ad_account_id' => $accountId,
            'campaign_id' => $campaignId,
            'ad_id' => $adId,
            'type' => 'rejection',
            'severity' => $severity,
            'description' => $reason,
            'status' => 'open',
        ]);
    }

    public function resolveIssue($logId, $notes = null)
    {
        $log = MetaComplianceLog::findOrFail($logId);
        $log->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolution_notes' => $notes,
        ]);

        return $log;
    }

    public function checkSpendingLimits()
    {
        $limits = MetaSpendingLimit::where('status', 'active')->get();
        $results = [];

        foreach ($limits as $limit) {
            $currentSpend = $this->getCurrentSpend($limit);
            $limit->update(['current_spend' => $currentSpend]);

            if ($limit->daily_limit && $currentSpend >= $limit->daily_limit) {
                $this->handleLimitReached($limit);
                $results[] = [
                    'limit_id' => $limit->id,
                    'action' => 'limit_reached',
                    'spend' => $currentSpend,
                    'limit' => $limit->daily_limit,
                ];
            } elseif ($limit->daily_limit && 
                      ($currentSpend / $limit->daily_limit) >= ($limit->alert_threshold / 100)) {
                $results[] = [
                    'limit_id' => $limit->id,
                    'action' => 'near_limit',
                    'spend' => $currentSpend,
                    'limit' => $limit->daily_limit,
                    'percentage' => round(($currentSpend / $limit->daily_limit) * 100, 2),
                ];
            }
        }

        return $results;
    }

    protected function getCurrentSpend($limit)
    {
        if ($limit->scope === 'account') {
            return MetaCampaign::where('ad_account_id', $limit->ad_account_id)
                ->sum('insights->spend');
        } elseif ($limit->scope === 'campaign') {
            $campaign = MetaCampaign::find($limit->entity_id);
            return $campaign?->insights['spend'] ?? 0;
        }

        return 0;
    }

    protected function handleLimitReached($limit)
    {
        $limit->update(['status' => 'triggered']);

        if ($limit->action_on_limit === 'pause') {
            $this->pauseCampaigns($limit);
        }

        Log::warning("Spending limit reached for account {$limit->ad_account_id}");
    }

    protected function pauseCampaigns($limit)
    {
        if ($limit->scope === 'account') {
            $campaigns = MetaCampaign::where('ad_account_id', $limit->ad_account_id)
                ->where('status', 'ACTIVE')
                ->get();
        } else {
            $campaigns = MetaCampaign::where('id', $limit->entity_id)->get();
        }

        foreach ($campaigns as $campaign) {
            try {
                $account = $campaign->adAccount;
                $this->graphService->setAccessToken($account->access_token);
                $this->graphService->updateCampaign($campaign->campaign_id, ['status' => 'PAUSED']);
                $campaign->update(['status' => 'PAUSED']);
            } catch (\Exception $e) {
                Log::error("Failed to pause campaign: " . $e->getMessage());
            }
        }
    }

    public function createSpendingLimit($data)
    {
        return MetaSpendingLimit::create($data);
    }

    public function getComplianceSummary($accountId)
    {
        return [
            'open_issues' => MetaComplianceLog::where('ad_account_id', $accountId)
                ->where('status', 'open')
                ->count(),
            'critical_issues' => MetaComplianceLog::where('ad_account_id', $accountId)
                ->where('status', 'open')
                ->where('severity', 'critical')
                ->count(),
            'resolved_this_week' => MetaComplianceLog::where('ad_account_id', $accountId)
                ->where('status', 'resolved')
                ->where('resolved_at', '>=', now()->subWeek())
                ->count(),
            'active_limits' => MetaSpendingLimit::where('ad_account_id', $accountId)
                ->where('status', 'active')
                ->count(),
            'triggered_limits' => MetaSpendingLimit::where('ad_account_id', $accountId)
                ->where('status', 'triggered')
                ->count(),
        ];
    }
}
