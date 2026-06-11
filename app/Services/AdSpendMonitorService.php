<?php

namespace App\Services;

use App\Models\CapiEventLog;
use App\Models\Meta\MetaCampaign;
use App\Models\Meta\MetaAdAccount;
use App\Services\Meta\FacebookGraphService;
use App\Services\MetaReportingService;
use Illuminate\Support\Facades\Log;

class AdSpendMonitorService
{
    public function __construct(
        private FacebookGraphService $graph,
        private MetaReportingService $reporting,
        private AdAccountHealthService $health,
    ) {}

    public function checkAllPlatforms(): array
    {
        $alerts = [];
        $platforms = ['facebook', 'tiktok', 'google', 'snapchat', 'pinterest', 'twitter', 'linkedin'];

        foreach ($platforms as $platform) {
            $result = $this->checkPlatform($platform);
            if (!empty($result)) {
                $alerts[$platform] = $result;
            }
        }

        return $alerts;
    }

    public function checkPlatform(string $platform): array
    {
        $alerts = [];
        $config = config('tracking.spend', []);
        if (!($config['enabled'] ?? true)) {
            return $alerts;
        }

        $campaigns = MetaCampaign::where('status', 'ACTIVE')->get();

        foreach ($campaigns as $campaign) {
            $result = $this->analyzeCampaign($campaign, $platform, $config);
            if (!empty($result)) {
                $alerts[] = $result;
            }
        }

        return $alerts;
    }

    public function analyzeCampaign(MetaCampaign $campaign, string $platform, array $config): ?array
    {
        $insights = $campaign->insights;
        if (empty($insights) || !isset($insights['spend'])) {
            return null;
        }

        $currentSpend = (float) ($insights['spend'] ?? 0);
        $dailyBudget = (float) ($campaign->daily_budget ?? 0);
        $conversions = (int) ($insights['actions']['purchase'] ?? $insights['actions']['lead'] ?? 0);
        $impressions = (int) ($insights['impressions'] ?? 0);
        $clicks = (int) ($insights['clicks'] ?? 0);

        if ($conversions < ($config['min_conversions_for_analysis'] ?? 5)) {
            return null;
        }

        $cpa = $conversions > 0 ? $currentSpend / $conversions : 0;

        $historicalCpa = $this->getHistoricalCpa($campaign, $platform, $config['cpa_lookback_hours'] ?? 24);
        $emq = $this->getCurrentEmq($platform);

        $alerts = [];

        if ($impressions > 0 && $clicks > 0) {
            $ctr = $clicks / $impressions;
            if ($ctr > 0.15) {
                $alerts[] = [
                    'type' => 'suspicious_ctr',
                    'severity' => 'warning',
                    'title' => 'نسبة نقر مرتفعة بشكل غير طبيعي',
                    'body' => "الحملة {$campaign->name}: CTR {$ctr} أعلى من 15% - قد يكون هناك نقرات وهمية",
                    'trigger_value' => round($ctr * 100, 2),
                    'threshold' => 15,
                ];
            }
        }

        if ($dailyBudget > 0) {
            $spendPct = ($currentSpend / $dailyBudget) * 100;

            if ($spendPct >= ($config['daily_budget_critical_pct'] ?? 100)) {
                $alerts[] = [
                    'type' => 'daily_budget_exceeded',
                    'severity' => 'critical',
                    'title' => 'تم تجاوز الميزانية اليومية',
                    'body' => "الحملة {$campaign->name}: أنفقت {$currentSpend} من أصل {$dailyBudget}",
                    'trigger_value' => $spendPct,
                    'threshold' => $config['daily_budget_critical_pct'] ?? 100,
                ];
            } elseif ($spendPct >= ($config['daily_budget_warning_pct'] ?? 80)) {
                $alerts[] = [
                    'type' => 'daily_budget_warning',
                    'severity' => 'warning',
                    'title' => 'اقتراب من الحد اليومي للميزانية',
                    'body' => "الحملة {$campaign->name}: أنفقت {$spendPct}% من الميزانية ({$currentSpend} / {$dailyBudget})",
                    'trigger_value' => $spendPct,
                    'threshold' => $config['daily_budget_warning_pct'] ?? 80,
                ];
            }
        }

        if ($historicalCpa > 0 && $cpa > 0) {
            $cpaRatio = $cpa / $historicalCpa;
            if ($cpaRatio >= ($config['cpa_spike_threshold'] ?? 2.0)) {
                $alerts[] = [
                    'type' => 'cpa_spike',
                    'severity' => 'critical',
                    'title' => 'ارتفاع حاد في تكلفة الاكتساب',
                    'body' => "الحملة {$campaign->name}: CPA الحالي {$cpa} مقابل {$historicalCpa} تاريخياً (نسبة {$cpaRatio}x)",
                    'trigger_value' => round($cpaRatio, 2),
                    'threshold' => $config['cpa_spike_threshold'] ?? 2.0,
                ];
            }
        }

        if ($emq < ($config['emq_drop_threshold'] ?? 0.7)) {
            $alerts[] = [
                'type' => 'emq_drop',
                'severity' => 'critical',
                'title' => 'انخفاض جودة التتبع (EMQ)',
                'body' => "منصة {$platform}: EMQ الحالي {$emq} - أقل من الحد المسموح {$config['emq_drop_threshold']}",
                'trigger_value' => round($emq, 2),
                'threshold' => $config['emq_drop_threshold'] ?? 0.7,
            ];
        }

        if (empty($alerts)) {
            return null;
        }

        return [
            'campaign_id' => $campaign->campaign_id,
            'campaign_name' => $campaign->name,
            'platform' => $platform,
            'insights' => $alerts,
        ];
    }

    private function getHistoricalCpa(MetaCampaign $campaign, string $platform, int $hours): float
    {
        $events = CapiEventLog::where('platform', $platform)
            ->where('event_name', 'Purchase')
            ->where('success', true)
            ->where('created_at', '>=', now()->subHours($hours))
            ->count();

        if ($events === 0) {
            return 0;
        }

        $priorSpend = (float) ($campaign->insights['spend'] ?? 0);
        if ($priorSpend === 0) {
            return 0;
        }

        return $priorSpend / $events;
    }

    private function getCurrentEmq(string $platform): float
    {
        $total = CapiEventLog::where('platform', $platform)
            ->where('created_at', '>=', now()->subDay())
            ->count();

        if ($total === 0) {
            return 1.0;
        }

        $successful = CapiEventLog::where('platform', $platform)
            ->where('success', true)
            ->where('created_at', '>=', now()->subDay())
            ->count();

        return $successful / max($total, 1);
    }
}
