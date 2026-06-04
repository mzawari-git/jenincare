<?php

namespace App\Console\Commands;

use App\Models\SkinScan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanupOldScans extends Command
{
    protected $signature = 'scans:cleanup
        {--older-than=90 : Delete scans older than this many days}
        {--dry-run : Preview what would be deleted without actually deleting}';

    protected $description = 'Clean up old skin scans and their associated images';

    public function handle(): int
    {
        $days = (int) $this->option('older-than');
        $dryRun = $this->option('dry-run');
        $cutoff = now()->subDays($days);

        $this->info("Finding scans older than {$days} days (before {$cutoff->toDateString()})...");

        $oldScans = SkinScan::where('created_at', '<', $cutoff)->get();
        $count = $oldScans->count();

        if ($count === 0) {
            $this->info('No old scans found.');
            return Command::SUCCESS;
        }

        $this->warn("Found {$count} scan(s) to " . ($dryRun ? 'review' : 'delete'));

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $deleted = 0;
        $freedBytes = 0;

        foreach ($oldScans as $scan) {
            if ($dryRun) {
                $this->line(" [DRY RUN] Would delete scan {$scan->id} from {$scan->created_at}");
                $bar->advance();
                continue;
            }

            $paths = [$scan->image_path];
            foreach (['rgb', 'cross', 'parallel', 'uv'] as $mode) {
                $field = $mode . '_path';
                if ($scan->$field) {
                    $paths[] = $scan->$field;
                }
            }

            foreach ($paths as $path) {
                if ($path && Storage::disk('public')->exists($path)) {
                    $freedBytes += Storage::disk('public')->size($path);
                    Storage::disk('public')->delete($path);
                }
            }

            $scan->analysisImages()->delete();
            $scan->heatmapPoints()->delete();
            $scan->defects()->delete();
            $scan->timelineEvents()->delete();
            $scan->generalTips()->delete();
            $scan->delete();

            $deleted++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if (!$dryRun) {
            $freedMb = round($freedBytes / 1024 / 1024, 2);
            Log::info("CleanupOldScans: deleted {$deleted} scans, freed {$freedMb} MB");
            $this->info("Deleted {$deleted} scan(s), freed {$freedMb} MB");
        }

        return Command::SUCCESS;
    }
}
