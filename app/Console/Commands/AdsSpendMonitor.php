<?php

namespace App\Console\Commands;

use App\Models\AdAlert;
use App\Services\AdAutoPauseService;
use App\Services\AdSpendMonitorService;
use App\Services\AlertNotifier;
use Illuminate\Console\Command;

class AdsSpendMonitor extends Command
{
    protected $signature = 'ads:spend-monitor {--platform=all}';
    protected $description = 'Monitor ad spend for anomalies and auto-pause if needed';

    public function handle(AdSpendMonitorService $spendMonitor, AdAutoPauseService $autoPause, AlertNotifier $notifier)
    {
        $this->info('Running ad spend anomaly detection...');

        $platform = $this->option('platform');
        $results = $platform === 'all'
            ? $spendMonitor->checkAllPlatforms()
            : [$platform => $spendMonitor->checkPlatform($platform)];

        $totalAlerts = 0;
        foreach ($results as $platform => $campaignAlerts) {
            foreach ($campaignAlerts as $campaignAlert) {
                foreach ($campaignAlert['insights'] as $alert) {
                    $this->warn("  [{$platform}] {$alert['title']}");

                    $adAlert = AdAlert::create([
                        'platform' => $platform,
                        'type' => $alert['type'],
                        'severity' => $alert['severity'],
                        'title' => $alert['title'],
                        'body' => $alert['body'],
                        'data' => [
                            'campaign_id' => $campaignAlert['campaign_id'],
                            'campaign_name' => $campaignAlert['campaign_name'],
                            'trigger_value' => $alert['trigger_value'],
                            'threshold' => $alert['threshold'],
                        ],
                        'campaign_id' => $campaignAlert['campaign_id'],
                    ]);

                    $totalAlerts++;

                    $notifier->send([
                        'channel' => 'all',
                        'type' => 'spend_anomaly',
                        'platform' => $platform,
                        'title' => ($alert['severity'] === 'critical' ? '🔴' : '🟡') . " {$alert['title']}",
                        'body' => $alert['body'],
                        'severity' => $alert['severity'],
                    ]);

                    if ($alert['severity'] === 'critical' && config('tracking.auto_pause.enabled', true)) {
                        $campaign = \App\Models\Meta\MetaCampaign::where('campaign_id', $campaignAlert['campaign_id'])->first();
                        if ($campaign && $campaign->status === 'ACTIVE') {
                            $autoPause->pauseForSpendAnomaly($campaign, $platform, $alert);
                            $this->warn("    → Auto-paused campaign {$campaign->name}");
                        }
                    }
                }
            }
        }

        $this->info("Spend monitor complete. {$totalAlerts} alerts generated.");

        return Command::SUCCESS;
    }
}
