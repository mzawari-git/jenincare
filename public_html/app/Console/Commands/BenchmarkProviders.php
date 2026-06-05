<?php

namespace App\Console\Commands;

use App\Models\SkinScan;
use App\Services\AI\AIOrchestrator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BenchmarkProviders extends Command
{
    protected $signature = 'ai:benchmark
        {--scan-id= : Specific scan ID to use (defaults to latest completed scan)}
        {--all : Test all providers including inactive ones}';

    protected $description = 'Benchmark all AI providers against a reference scan';

    public function handle(AIOrchestrator $orchestrator): int
    {
        if ($scanId = $this->option('scan-id')) {
            $scan = SkinScan::findOrFail($scanId);
        } else {
            $scan = SkinScan::whereNotNull('image_path')
                ->latest()
                ->first();
        }

        if (!$scan) {
            $this->error('No scan found with an image path.');
            return Command::FAILURE;
        }

        $this->info("Benchmarking providers using scan: {$scan->id}");
        $this->newLine();

        $providers = \App\Models\AIProvider::query();
        if (!$this->option('all')) {
            $providers->where('is_active', true);
        }
        $providers = $providers->orderBy('priority')->get();

        if ($providers->isEmpty()) {
            $this->error('No AI providers found.');
            return Command::FAILURE;
        }

        $headers = ['Provider', 'Engine', 'Status', 'Score', 'Defects', 'Time (s)', 'Confidence'];
        $rows = [];

        foreach ($providers as $provider) {
            $this->line("  Testing {$provider->name} ({$provider->driver_key})...");

            $start = microtime(true);

            try {
                $result = $orchestrator->analyze($scan, $provider->driver_key);
                $elapsed = round(microtime(true) - $start, 2);

                $rows[] = [
                    $provider->name,
                    $provider->engine_type,
                    '<info>OK</info>',
                    $result->overallHealthScore,
                    count($result->defects),
                    $elapsed,
                    round($result->confidence / 100, 2),
                ];
            } catch (\Throwable $e) {
                $elapsed = round(microtime(true) - $start, 2);
                $rows[] = [
                    $provider->name,
                    $provider->engine_type,
                    "<error>FAIL</error>",
                    '-',
                    '-',
                    $elapsed,
                    $e->getMessage(),
                ];
            }
        }

        $this->table($headers, $rows);
        $this->newLine();

        Log::info('AI Benchmark completed', [
            'scan_id' => $scan->id,
            'providers_tested' => $providers->count(),
        ]);

        return Command::SUCCESS;
    }
}
