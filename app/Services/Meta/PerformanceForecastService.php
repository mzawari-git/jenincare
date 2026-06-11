<?php

namespace App\Services\Meta;

use App\Models\Meta\MetaCampaign;
use App\Models\Meta\MetaAdAccount;

class PerformanceForecastService
{
    public function forecastCampaignPerformance(int $campaignId, array $scenarios = []): array
    {
        $campaign = MetaCampaign::with('adAccount')->findOrFail($campaignId);
        $insights = $campaign->insights[0] ?? [];

        if (empty($insights)) {
            return [
                'has_data' => false,
                'message' => 'لا توجد بيانات كافية للتنبؤ',
            ];
        }

        $currentSpend = $insights['spend'] ?? 0;
        $currentImpressions = $insights['impressions'] ?? 0;
        $currentClicks = $insights['clicks'] ?? 0;
        $currentConversions = $this->extractConversions($insights);
        $currentRevenue = $this->extractConversionValue($insights);

        $days = 30;
        $dailySpend = $days > 0 ? $currentSpend / $days : 0;
        $ctr = $currentImpressions > 0 ? ($currentClicks / $currentImpressions) * 100 : 0;
        $cvr = $currentClicks > 0 ? ($currentConversions / $currentClicks) * 100 : 0;
        $aov = $currentConversions > 0 ? $currentRevenue / $currentConversions : 0;

        $forecast30 = $this->calculateForecast($dailySpend, $ctr, $cvr, $aov, 30, 1);
        $forecast60 = $this->calculateForecast($dailySpend, $ctr, $cvr, $aov, 60, 1);
        $forecast90 = $this->calculateForecast($dailySpend, $ctr, $cvr, $aov, 90, 1);

        $scenarioResults = [];
        foreach ($scenarios as $key => $scenario) {
            $budgetMultiplier = $scenario['budget_multiplier'] ?? 1;
            $ctrChange = $scenario['ctr_improvement'] ?? 0;
            $cvrChange = $scenario['cvr_improvement'] ?? 0;

            $adjustedCtr = $ctr * (1 + $ctrChange / 100);
            $adjustedCvr = $cvr * (1 + $cvrChange / 100);
            $adjustedDailySpend = $dailySpend * $budgetMultiplier;

            $scenarioResults[$key] = [
                'name' => $scenario['name'] ?? "سيناريو {$key}",
                'description' => $scenario['description'] ?? '',
                'forecast_30' => $this->calculateForecast($adjustedDailySpend, $adjustedCtr, $adjustedCvr, $aov, 30, $budgetMultiplier),
                'forecast_60' => $this->calculateForecast($adjustedDailySpend, $adjustedCtr, $adjustedCvr, $aov, 60, $budgetMultiplier),
                'forecast_90' => $this->calculateForecast($adjustedDailySpend, $adjustedCtr, $adjustedCvr, $aov, 90, $budgetMultiplier),
            ];
        }

        return [
            'has_data' => true,
            'campaign_name' => $campaign->name,
            'current_metrics' => [
                'daily_spend' => round($dailySpend, 2),
                'ctr' => round($ctr, 2) . '%',
                'cvr' => round($cvr, 2) . '%',
                'aov' => round($aov, 2),
                'currency' => $campaign->adAccount->currency ?? 'ILS',
            ],
            'baseline_forecast' => [
                '30_days' => $forecast30,
                '60_days' => $forecast60,
                '90_days' => $forecast90,
            ],
            'scenarios' => $scenarioResults,
            'default_scenarios' => $this->getDefaultScenarios($dailySpend, $ctr, $cvr, $aov),
        ];
    }

    public function getAccountForecast(int $accountId): array
    {
        $account = MetaAdAccount::findOrFail($accountId);
        $campaigns = MetaCampaign::where('ad_account_id', $accountId)->get();

        if ($campaigns->isEmpty()) {
            return ['has_data' => false, 'message' => 'لا توجد حملات'];
        }

        $totalDailySpend = 0;
        $totalConversions = 0;
        $totalRevenue = 0;

        foreach ($campaigns as $campaign) {
            $insights = $campaign->insights[0] ?? [];
            $spend = $insights['spend'] ?? 0;
            $totalDailySpend += $spend / 30;
            $totalConversions += $this->extractConversions($insights);
            $totalRevenue += $this->extractConversionValue($insights);
        }

        $monthlyConversions = $totalConversions;
        $monthlyRevenue = $totalRevenue;

        return [
            'has_data' => true,
            'account_name' => $account->name,
            'currency' => $account->currency ?? 'ILS',
            'current_monthly' => [
                'spend' => round($totalDailySpend * 30, 2),
                'conversions' => $monthlyConversions,
                'revenue' => round($monthlyRevenue, 2),
                'roas' => $totalDailySpend > 0 ? round($monthlyRevenue / ($totalDailySpend * 30), 2) : 0,
            ],
            'forecast_30' => [
                'estimated_spend' => round($totalDailySpend * 30, 2),
                'estimated_conversions' => round($monthlyConversions * 1),
                'estimated_revenue' => round($monthlyRevenue * 1, 2),
                'estimated_roas' => $totalDailySpend > 0 ? round($monthlyRevenue / ($totalDailySpend * 30), 2) : 0,
            ],
            'forecast_60' => [
                'estimated_spend' => round($totalDailySpend * 60, 2),
                'estimated_conversions' => round($monthlyConversions * 2),
                'estimated_revenue' => round($monthlyRevenue * 2, 2),
            ],
            'forecast_90' => [
                'estimated_spend' => round($totalDailySpend * 90, 2),
                'estimated_conversions' => round($monthlyConversions * 3),
                'estimated_revenue' => round($monthlyRevenue * 3, 2),
            ],
        ];
    }

    private function calculateForecast(float $dailySpend, float $ctr, float $cvr, float $aov, int $days, float $budgetMultiplier): array
    {
        $estimatedSpend = $dailySpend * $days * $budgetMultiplier;
        $estimatedImpressions = $dailySpend > 0 ? ($dailySpend / ($dailySpend / 1000)) * 1000 * $days : 0;
        $estimatedClicks = $ctr > 0 ? ($estimatedImpressions * $ctr / 100) : 0;
        $estimatedConversions = $cvr > 0 ? ($estimatedClicks * $cvr / 100) : 0;
        $estimatedRevenue = $estimatedConversions * $aov;

        return [
            'estimated_spend' => round($estimatedSpend, 2),
            'estimated_impressions' => round($estimatedImpressions),
            'estimated_clicks' => round($estimatedClicks),
            'estimated_conversions' => round($estimatedConversions),
            'estimated_revenue' => round($estimatedRevenue, 2),
            'estimated_roas' => $estimatedSpend > 0 ? round($estimatedRevenue / $estimatedSpend, 2) : 0,
        ];
    }

    private function getDefaultScenarios(float $dailySpend, float $ctr, float $cvr, float $aov): array
    {
        return [
            'conservative' => $this->calculateForecast($dailySpend, $ctr, $cvr, $aov, 30, 0.7),
            'moderate' => $this->calculateForecast($dailySpend, $ctr, $cvr, $aov, 30, 1),
            'aggressive' => $this->calculateForecast($dailySpend, $ctr * 1.1, $cvr * 1.1, $aov, 30, 1.5),
            'optimized' => $this->calculateForecast($dailySpend * 1.2, $ctr * 1.15, $cvr * 1.15, $aov * 1.05, 30, 1.2),
        ];
    }

    private function extractConversions(array $insights): int
    {
        $actions = $insights['actions'] ?? [];
        if (is_array($actions)) {
            foreach ($actions as $action) {
                if (in_array($action['action_type'] ?? '', ['purchase', 'lead', 'conversion'])) {
                    return (int) ($action['value'] ?? 0);
                }
            }
        }
        return $insights['conversions'] ?? 0;
    }

    private function extractConversionValue(array $insights): float
    {
        $conversionValues = $insights['conversion_values'] ?? [];
        if (is_array($conversionValues)) {
            foreach ($conversionValues as $value) {
                if (isset($value['value'])) {
                    return (float) $value['value'];
                }
            }
        }
        $actions = $insights['actions'] ?? [];
        if (is_array($actions)) {
            foreach ($actions as $action) {
                if (($action['action_type'] ?? '') === 'purchase' && isset($action['value'])) {
                    return (float) $action['value'];
                }
            }
        }
        return 0;
    }
}
