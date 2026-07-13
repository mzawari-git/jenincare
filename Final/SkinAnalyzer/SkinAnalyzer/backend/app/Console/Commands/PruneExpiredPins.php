<?php

namespace App\Console\Commands;

use App\Models\SkinAnalysisPin;
use Illuminate\Console\Command;

class PruneExpiredPins extends Command
{
    protected $signature = 'skinanalyzer:prune-expired-pins';

    protected $description = 'Delete or expire PINs that have passed their expiry time.';

    public function handle(): int
    {
        $this->info('Pruning expired PINs...');

        $expiredPins = SkinAnalysisPin::where('expires_at', '<', now())
            ->where('is_used', false);

        $count = $expiredPins->count();

        if ($count === 0) {
            $this->info('No expired PINs found.');

            return self::SUCCESS;
        }

        $expiredPins->delete();

        $this->info("Successfully pruned {$count} expired PIN(s).");

        return self::SUCCESS;
    }
}
