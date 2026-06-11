<?php

namespace App\Services\Meta;

use App\Models\Meta\MetaCampaign;
use App\Models\Meta\MetaAdSet;
use App\Models\Meta\MetaAdAccount;
use Illuminate\Support\Facades\DB;

class BudgetOptimizerService
{
    public function getAccountBudgetRecommendations(int $accountId): array
    {
        $account = MetaAdAccount::findOrFail($accountId);
        $campaigns = MetaCampaign::where('ad_account_id', $accountId)
            ->where('status', 'ACTIVE')
            ->get();

        if ($campaigns->isEmpty()) {
            return [
                'has_data' => false,
                'message' => 'لا توجد حملات نشطة لتحليلها',
            ];
        }

        $totalSpend = 0;
        $totalConversions = 0;
        $totalRevenue = 0;
        $campaignAnalyses = [];

        foreach ($campaigns as $campaign) {
            $insights = $campaign->insights[0] ?? [];
            $spend = $insights['spend'] ?? 0;
            $conversions = $this->extractConversions($insights);
            $revenue = $this->extractConversionValue($insights);

            $totalSpend += $spend;
            $totalConversions += $conversions;
            $totalRevenue += $revenue;

            $roas = $spend > 0 ? round($revenue / $spend, 2) : 0;
            $cpa = $conversions > 0 ? round($spend / $conversions, 2) : 0;

            $campaignAnalyses[] = [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'objective' => $campaign->objective,
                'daily_budget' => $campaign->daily_budget,
                'current_spend' => round($spend, 2),
                'conversions' => $conversions,
                'revenue' => round($revenue, 2),
                'roas' => $roas,
                'cpa' => $cpa,
                'recommendation' => $this->getCampaignBudgetRecommendation($roas, $cpa, $campaign->daily_budget),
            ];
        }

        $overallRoas = $totalSpend > 0 ? round($totalRevenue / $totalSpend, 2) : 0;
        $overallCpa = $totalConversions > 0 ? round($totalSpend / $totalConversions, 2) : 0;

        return [
            'has_data' => true,
            'account' => [
                'name' => $account->name,
                'currency' => $account->currency ?? 'ILS',
                'spend_cap' => $account->spend_cap,
                'amount_spent' => $account->amount_spent,
            ],
            'overview' => [
                'total_spend' => round($totalSpend, 2),
                'total_conversions' => $totalConversions,
                'total_revenue' => round($totalRevenue, 2),
                'overall_roas' => $overallRoas,
                'overall_cpa' => $overallCpa,
                'active_campaigns' => $campaigns->count(),
            ],
            'campaigns' => $campaignAnalyses,
            'budget_distribution' => $this->suggestBudgetDistribution($campaignAnalyses, $account->spend_cap),
            'summary_recommendation' => $this->getSummaryRecommendation($overallRoas, $overallCpa, $campaignAnalyses),
        ];
    }

    public function getBudgetDistributionRecommendation(int $accountId): array
    {
        $account = MetaAdAccount::findOrFail($accountId);
        $campaigns = MetaCampaign::where('ad_account_id', $accountId)
            ->where('status', 'ACTIVE')
            ->get();

        if ($campaigns->isEmpty()) {
            return ['message' => 'لا توجد حملات نشطة'];
        }

        $totalBudget = $campaigns->sum('daily_budget');
        if ($totalBudget <= 0) {
            $totalBudget = 100;
        }

        $distribution = [];
        foreach ($campaigns as $campaign) {
            $insights = $campaign->insights[0] ?? [];
            $spend = $insights['spend'] ?? 0;
            $revenue = $this->extractConversionValue($insights);
            $roas = $spend > 0 ? $revenue / $spend : 0;

            $weight = max(0.1, $roas);
            $distribution[] = [
                'campaign_id' => $campaign->id,
                'campaign_name' => $campaign->name,
                'current_budget' => $campaign->daily_budget,
                'current_share' => $totalBudget > 0 ? round(($campaign->daily_budget / $totalBudget) * 100, 1) : 0,
                'roas' => round($roas, 2),
                'recommended_share' => round(($weight / max(0.1, array_sum(array_column($distribution, 'weight') ?: [1]))) * 100, 1),
                'recommended_budget' => 0,
            ];
        }

        $totalWeight = array_sum(array_column($distribution, 'roas'));
        $totalWeight = max(0.1, $totalWeight);

        foreach ($distribution as &$d) {
            $d['recommended_share'] = round(($d['roas'] / $totalWeight) * 100, 1);
            $d['recommended_budget'] = round(($d['recommended_share'] / 100) * $totalBudget, 2);
        }

        return [
            'total_budget' => $totalBudget,
            'currency' => $account->currency ?? 'ILS',
            'distribution' => $distribution,
            'note' => 'التوزيع المقترح بناءً على أداء ROAS لكل حملة',
        ];
    }

    public function getPerformanceScore(int $accountId): array
    {
        $campaigns = MetaCampaign::where('ad_account_id', $accountId)->get();
        if ($campaigns->isEmpty()) {
            return ['score' => 0, 'grade' => 'N/A', 'message' => 'لا توجد بيانات'];
        }

        $metrics = ['impressions' => 0, 'clicks' => 0, 'spend' => 0, 'reach' => 0];
        foreach ($campaigns as $campaign) {
            $insights = $campaign->insights[0] ?? [];
            $metrics['impressions'] += $insights['impressions'] ?? 0;
            $metrics['clicks'] += $insights['clicks'] ?? 0;
            $metrics['spend'] += $insights['spend'] ?? 0;
            $metrics['reach'] += $insights['reach'] ?? 0;
        }

        $ctr = $metrics['impressions'] > 0 ? ($metrics['clicks'] / $metrics['impressions']) * 100 : 0;
        $cpc = $metrics['clicks'] > 0 ? $metrics['spend'] / $metrics['clicks'] : 0;
        $frequency = $metrics['reach'] > 0 ? $metrics['impressions'] / $metrics['reach'] : 0;

        $ctrScore = min(100, ($ctr / 2) * 100);
        $cpcScore = $cpc > 0 ? max(0, 100 - ($cpc * 10)) : 0;
        $freqScore = $frequency > 0 ? max(0, 100 - (($frequency - 1) * 20)) : 100;

        $overallScore = round(($ctrScore * 0.4) + ($cpcScore * 0.35) + ($freqScore * 0.25));

        $grade = match (true) {
            $overallScore >= 90 => 'A+',
            $overallScore >= 80 => 'A',
            $overallScore >= 70 => 'B+',
            $overallScore >= 60 => 'B',
            $overallScore >= 50 => 'C+',
            $overallScore >= 40 => 'C',
            $overallScore >= 30 => 'D',
            default => 'F',
        };

        return [
            'score' => $overallScore,
            'grade' => $grade,
            'metrics' => [
                'ctr' => round($ctr, 2),
                'cpc' => round($cpc, 4),
                'frequency' => round($frequency, 2),
                'total_impressions' => $metrics['impressions'],
                'total_clicks' => $metrics['clicks'],
                'total_spend' => round($metrics['spend'], 2),
            ],
            'recommendations' => $this->getScoreRecommendations($overallScore, $ctr, $cpc, $frequency),
        ];
    }

    private function getCampaignBudgetRecommendation(float $roas, float $cpa, ?float $currentBudget): string
    {
        if ($roas >= 3) {
            return 'أداء ممتاز - زيادة الميزانية بنسبة 20-30%';
        }
        if ($roas >= 1.5) {
            return 'أداء جيد - يمكن زيادة الميزانية تدريجياً';
        }
        if ($roas >= 1) {
            return 'أداء مقبول - مراقبة الأداء وتحسين الاستهداف';
        }
        if ($cpa > 0 && $currentBudget && $cpa > $currentBudget * 0.5) {
            return 'تكلفة التحويل مرتفعة - تحسين الإعلان أو خفض الميزانية';
        }
        return 'أداء ضعيف - مراجعة الاستهداف والمحتوى الإعلاني';
    }

    private function suggestBudgetDistribution(array $campaigns, ?float $spendCap): array
    {
        if (empty($campaigns)) {
            return [];
        }

        $topPerformers = array_filter($campaigns, fn($c) => ($c['roas'] ?? 0) >= 1.5);
        $underPerformers = array_filter($campaigns, fn($c) => ($c['roas'] ?? 0) < 1);

        return [
            'increase_budget' => array_values(array_map(fn($c) => [
                'name' => $c['name'],
                'current_budget' => $c['daily_budget'],
                'suggested_increase' => '20-30%',
            ], array_slice($topPerformers, 0, 3))),
            'review_campaigns' => array_values(array_map(fn($c) => [
                'name' => $c['name'],
                'current_budget' => $c['daily_budget'],
                'suggestion' => 'مراجعة أو إيقاف مؤقت',
            ], array_slice($underPerformers, 0, 3))),
            'spend_cap_available' => $spendCap ? max(0, $spendCap - array_sum(array_column($campaigns, 'current_spend'))) : null,
        ];
    }

    private function getSummaryRecommendation(float $roas, float $cpa, array $campaigns): array
    {
        $recommendations = [];

        if ($roas < 1) {
            $recommendations[] = 'العائد على الإنفاق أقل من 1 - يوصى بمراجعة شاملة للحملات';
        } elseif ($roas < 2) {
            $recommendations[] = 'العائد على الإنفاق مقبول - يمكن التحسين باختبار إعلانات جديدة';
        } else {
            $recommendations[] = 'العائد على الإنفاق جيد - استمرار في تحسين الأداء';
        }

        $highCpaCampaigns = array_filter($campaigns, fn($c) => ($c['cpa'] ?? 0) > 0 && ($c['roas'] ?? 0) < 1);
        if (!empty($highCpaCampaigns)) {
            $names = implode('، ', array_column(array_slice($highCpaCampaigns, 0, 3), 'name'));
            $recommendations[] = "تكلفة التحويل مرتفعة في: {$names}";
        }

        $lowRoasCampaigns = array_filter($campaigns, fn($c) => ($c['roas'] ?? 0) >= 3);
        if (!empty($lowRoasCampaigns)) {
            $names = implode('، ', array_column(array_slice($lowRoasCampaigns, 0, 3), 'name'));
            $recommendations[] = "حملات عالية الأداء تستحق زيادة الميزانية: {$names}";
        }

        return $recommendations;
    }

    private function getScoreRecommendations(int $score, float $ctr, float $cpc, float $frequency): array
    {
        $recommendations = [];

        if ($ctr < 0.5) {
            $recommendations[] = 'نسبة النقر منخفضة - جرب عناوين وصور مختلفة';
        }
        if ($cpc > 0.5) {
            $recommendations[] = 'تكلفة النقر مرتفعة - حسّن جودة الإعلان والاستهداف';
        }
        if ($frequency > 3) {
            $recommendations[] = 'تكرار المشاهدة مرتفع - جدد الجمهور أو الإعلانات';
        }
        if ($score < 50) {
            $recommendations[] = 'يوصى بمراجعة استراتيجية الإعلانات بالكامل';
        }
        if (empty($recommendations)) {
            $recommendations[] = 'أداء جيد - استمر في المراقبة والتحسين المستمر';
        }

        return $recommendations;
    }

    private function extractConversions(array $insights): int
    {
        $actions = $insights['actions'] ?? [];
        if (is_array($actions)) {
            foreach ($actions as $action) {
                if (($action['action_type'] ?? '') === 'purchase' || ($action['action_type'] ?? '') === 'lead') {
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
