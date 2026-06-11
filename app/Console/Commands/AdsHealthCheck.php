<?php

namespace App\Console\Commands;

use App\Models\AdAlert;
use App\Services\AdAccountHealthService;
use App\Services\AdAutoPauseService;
use App\Services\AlertNotifier;
use Illuminate\Console\Command;

class AdsHealthCheck extends Command
{
    protected $signature = 'ads:health-check {--platform=all}';
    protected $description = 'Check ad account health and auto-pause campaigns if score is critical';

    public function handle(AdAccountHealthService $health, AdAutoPauseService $autoPause, AlertNotifier $notifier)
    {
        $this->info('Running ad account health check...');

        $platform = $this->option('platform');
        $platforms = $platform === 'all'
            ? ['facebook', 'tiktok', 'google', 'snapchat', 'pinterest', 'twitter', 'linkedin']
            : [$platform];

        $scores = [];
        foreach ($platforms as $p) {
            $this->line("  Checking {$p}...");
            $result = $health->computeScore($p);
            $scores[$p] = $result;

            if ($result['status'] === 'critical') {
                $this->warn("  {$p}: CRITICAL ({$result['score']})");

                AdAlert::create([
                    'platform' => $p,
                    'type' => 'health_critical',
                    'severity' => 'critical',
                    'title' => "مؤشر صحة الحساب منخفض: {$p}",
                    'body' => "النتيجة: {$result['score']} - الحالة: {$result['status']}",
                    'data' => $result,
                ]);

                $notifier->send([
                    'channel' => 'all',
                    'type' => 'health_critical',
                    'platform' => $p,
                    'title' => "🔴 مؤشر صحة منخفض: {$p} ({$result['score']})",
                    'body' => "المؤشرات: " . json_encode($result['signals']),
                    'severity' => 'critical',
                ]);
            } elseif ($result['status'] === 'warning') {
                $this->line("  {$p}: WARNING ({$result['score']})");

                AdAlert::create([
                    'platform' => $p,
                    'type' => 'health_warning',
                    'severity' => 'warning',
                    'title' => "مؤشر صحة الحساب في المنطقة الصفراء: {$p}",
                    'body' => "النتيجة: {$result['score']} - يوصى باتخاذ إجراء",
                    'data' => $result,
                ]);
            } else {
                $this->info("  {$p}: HEALTHY ({$result['score']})");
            }
        }

        $pauseResults = $autoPause->evaluateAndPause();
        if (!empty($pauseResults) && !isset($pauseResults['skipped'])) {
            foreach ($pauseResults as $pr) {
                $this->warn("  Paused campaign: {$pr['campaign_name']} ({$pr['platform']})");
            }
        }

        $resumeResults = $autoPause->checkForResume();
        if (!empty($resumeResults) && !isset($resumeResults['skipped'])) {
            foreach ($resumeResults as $rr) {
                $this->info("  Resumed campaign: {$rr['campaign_id']}");
            }
        }

        $this->info('Health check complete.');

        return Command::SUCCESS;
    }
}
