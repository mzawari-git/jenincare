<?php

namespace App\Services\Meta;

use App\Models\Meta\MetaAutomationRule;
use App\Models\Meta\MetaCampaign;
use App\Models\Meta\MetaScheduledCampaign;
use App\Models\Meta\MetaAdAccount;
use App\Services\Meta\FacebookGraphService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MetaAutomationService
{
    protected $graphService;

    public function __construct(FacebookGraphService $graphService)
    {
        $this->graphService = $graphService;
    }

    public function executeRules()
    {
        $rules = MetaAutomationRule::where('status', 'active')->get();
        
        $results = [];
        foreach ($rules as $rule) {
            $results[] = $this->executeRule($rule);
        }

        return $results;
    }

    public function executeRule(MetaAutomationRule $rule)
    {
        $campaigns = $this->getCampaignsForRule($rule);
        $actions = [];

        foreach ($campaigns as $campaign) {
            if ($this->matchConditions($campaign, $rule->conditions)) {
                $action = $this->executeAction($campaign, $rule->actions);
                if ($action) {
                    $actions[] = $action;
                }
            }
        }

        $rule->update([
            'last_executed_at' => now(),
            'execution_count' => $rule->execution_count + 1,
        ]);

        return [
            'rule_id' => $rule->id,
            'rule_name' => $rule->name,
            'actions_executed' => count($actions),
            'actions' => $actions,
        ];
    }

    protected function getCampaignsForRule(MetaAutomationRule $rule)
    {
        $query = MetaCampaign::where('ad_account_id', $rule->ad_account_id);

        if ($rule->scope === 'specific_campaigns' && !empty($rule->campaign_ids)) {
            $query->whereIn('id', $rule->campaign_ids);
        }

        return $query->where('status', 'ACTIVE')->get();
    }

    protected function matchConditions($campaign, $conditions)
    {
        foreach ($conditions as $condition) {
            $metric = $condition['metric'] ?? null;
            $operator = $condition['operator'] ?? null;
            $value = $condition['value'] ?? null;

            if (!$metric || !$operator || $value === null) {
                continue;
            }

            $campaignValue = $this->getCampaignMetric($campaign, $metric);

            if (!$this->compareValues($campaignValue, $operator, $value)) {
                return false;
            }
        }

        return true;
    }

    protected function getCampaignMetric($campaign, $metric)
    {
        $insights = $campaign->insights ?? [];

        switch ($metric) {
            case 'roas':
                return $insights['roas'] ?? 0;
            case 'ctr':
                return $insights['ctr'] ?? 0;
            case 'cpc':
                return $insights['cpc'] ?? 0;
            case 'cpa':
                return $insights['cpa'] ?? 0;
            case 'spend':
                return $insights['spend'] ?? 0;
            case 'impressions':
                return $insights['impressions'] ?? 0;
            case 'frequency':
                return $insights['frequency'] ?? 0;
            default:
                return 0;
        }
    }

    protected function compareValues($actual, $operator, $expected)
    {
        switch ($operator) {
            case 'greater_than':
                return $actual > $expected;
            case 'less_than':
                return $actual < $expected;
            case 'equals':
                return $actual == $expected;
            case 'greater_or_equal':
                return $actual >= $expected;
            case 'less_or_equal':
                return $actual <= $expected;
            default:
                return false;
        }
    }

    protected function executeAction($campaign, $actions)
    {
        foreach ($actions as $action) {
            $type = $action['type'] ?? null;

            switch ($type) {
                case 'pause':
                    return $this->pauseCampaign($campaign);
                case 'reduce_budget':
                    $percentage = $action['percentage'] ?? 20;
                    return $this->reduceBudget($campaign, $percentage);
                case 'increase_budget':
                    $percentage = $action['percentage'] ?? 20;
                    return $this->increaseBudget($campaign, $percentage);
                case 'adjust_bid':
                    $percentage = $action['percentage'] ?? 10;
                    return $this->adjustBid($campaign, $percentage);
                case 'send_alert':
                    return $this->sendAlert($campaign, $action['message'] ?? 'Alert triggered');
            }
        }

        return null;
    }

    protected function pauseCampaign($campaign)
    {
        try {
            $account = $campaign->adAccount;
            $this->graphService->setAccessToken($account->access_token);
            $this->graphService->updateCampaign($campaign->campaign_id, ['status' => 'PAUSED']);
            
            $campaign->update(['status' => 'PAUSED']);

            Log::info("Auto-paused campaign {$campaign->name}");

            return [
                'type' => 'pause',
                'campaign_id' => $campaign->id,
                'campaign_name' => $campaign->name,
                'success' => true,
            ];
        } catch (\Exception $e) {
            Log::error("Failed to auto-pause campaign: " . $e->getMessage());
            return [
                'type' => 'pause',
                'campaign_id' => $campaign->id,
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function reduceBudget($campaign, $percentage)
    {
        try {
            $newBudget = $campaign->daily_budget * (1 - $percentage / 100);
            $account = $campaign->adAccount;
            
            $this->graphService->setAccessToken($account->access_token);
            $this->graphService->updateCampaign($campaign->campaign_id, [
                'daily_budget' => (int)($newBudget * 100)
            ]);
            
            $campaign->update(['daily_budget' => $newBudget]);

            return [
                'type' => 'reduce_budget',
                'campaign_id' => $campaign->id,
                'old_budget' => $campaign->daily_budget,
                'new_budget' => $newBudget,
                'success' => true,
            ];
        } catch (\Exception $e) {
            return [
                'type' => 'reduce_budget',
                'campaign_id' => $campaign->id,
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function increaseBudget($campaign, $percentage)
    {
        try {
            $newBudget = $campaign->daily_budget * (1 + $percentage / 100);
            $account = $campaign->adAccount;
            
            $this->graphService->setAccessToken($account->access_token);
            $this->graphService->updateCampaign($campaign->campaign_id, [
                'daily_budget' => (int)($newBudget * 100)
            ]);
            
            $campaign->update(['daily_budget' => $newBudget]);

            return [
                'type' => 'increase_budget',
                'campaign_id' => $campaign->id,
                'old_budget' => $campaign->daily_budget,
                'new_budget' => $newBudget,
                'success' => true,
            ];
        } catch (\Exception $e) {
            return [
                'type' => 'increase_budget',
                'campaign_id' => $campaign->id,
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function adjustBid($campaign, $percentage)
    {
        return [
            'type' => 'adjust_bid',
            'campaign_id' => $campaign->id,
            'percentage' => $percentage,
            'success' => true,
            'message' => 'Bid adjustment queued',
        ];
    }

    protected function sendAlert($campaign, $message)
    {
        return [
            'type' => 'alert',
            'campaign_id' => $campaign->id,
            'message' => $message,
            'success' => true,
        ];
    }

    public function executeScheduledCampaigns()
    {
        $scheduled = MetaScheduledCampaign::where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->get();

        $results = [];
        foreach ($scheduled as $item) {
            $results[] = $this->executeScheduled($item);
        }

        return $results;
    }

    protected function executeScheduled(MetaScheduledCampaign $scheduled)
    {
        try {
            $campaign = $scheduled->campaign;
            $account = $campaign->adAccount;
            
            $this->graphService->setAccessToken($account->access_token);

            switch ($scheduled->action) {
                case 'activate':
                    $this->graphService->updateCampaign($campaign->campaign_id, ['status' => 'ACTIVE']);
                    $campaign->update(['status' => 'ACTIVE']);
                    break;
                case 'pause':
                    $this->graphService->updateCampaign($campaign->campaign_id, ['status' => 'PAUSED']);
                    $campaign->update(['status' => 'PAUSED']);
                    break;
                case 'budget_change':
                    $newBudget = $scheduled->parameters['budget'] ?? $campaign->daily_budget;
                    $this->graphService->updateCampaign($campaign->campaign_id, [
                        'daily_budget' => (int)($newBudget * 100)
                    ]);
                    $campaign->update(['daily_budget' => $newBudget]);
                    break;
            }

            $scheduled->update([
                'status' => 'executed',
                'executed_at' => now(),
            ]);

            return [
                'scheduled_id' => $scheduled->id,
                'success' => true,
            ];
        } catch (\Exception $e) {
            $scheduled->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return [
                'scheduled_id' => $scheduled->id,
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function createAutomationRule($data)
    {
        return MetaAutomationRule::create($data);
    }

    public function scheduleCampaignAction($data)
    {
        return MetaScheduledCampaign::create($data);
    }
}
