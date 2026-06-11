<?php

namespace App\Console\Commands;

use App\Models\Meta\MetaAdAccount;
use App\Models\Meta\MetaCampaign;
use App\Services\Meta\FacebookGraphService;
use App\Services\TokenManagerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncAllAdAccounts extends Command
{
    protected $signature = 'ads:sync-all {--platform=meta : Platform to sync (meta, tiktok, google, snapchat, pinterest, twitter, linkedin)} {--dry-run : Show what would be synced without saving}';

    protected $description = 'Sync advertising campaigns and insights from all connected ad platforms';

    public function handle(
        FacebookGraphService $graph,
        TokenManagerService $tokens,
    ): int {
        $platform = $this->option('platform');

        if ($platform === 'meta' || $platform === 'all') {
            $this->syncMeta($graph, $tokens);
        }

        $this->info('Ad account sync completed.');
        return self::SUCCESS;
    }

    private function syncMeta(FacebookGraphService $graph, TokenManagerService $tokens): void
    {
        if (!$tokens->isConnected('meta')) {
            $this->warn('Meta is not connected. Run OAuth flow first.');
            return;
        }

        $this->info('Syncing Meta ad accounts...');

        $accounts = MetaAdAccount::where('is_active', true)->get();

        if ($accounts->isEmpty()) {
            $this->warn('No active Meta ad accounts found.');
            return;
        }

        $tokenData = $tokens->get('meta');
        $accessToken = $tokenData['access_token'] ?? null;

        if (!$accessToken) {
            $token = $accounts->first()?->access_token;
            if (!$token) {
                $this->error('No valid Meta access token found.');
                return;
            }
            $accessToken = $token;
        }

        $graph->setUserAccessToken($accessToken);

        foreach ($accounts as $account) {
            try {
                $graph->setUserAccessToken($account->access_token ?? $accessToken);
                $campaigns = $graph->getCampaigns($account->ad_account_id);

                if (empty($campaigns)) {
                    $this->line("  No campaigns found for account: {$account->name} ({$account->ad_account_id})");
                    continue;
                }

                $count = 0;
                foreach ($campaigns as $fbCamp) {
                    if ($this->option('dry-run')) {
                        $this->line("  [DRY RUN] Would sync: {$fbCamp['name']} ({$fbCamp['id']})");
                        continue;
                    }

                    MetaCampaign::updateOrCreate(
                        ['campaign_id' => $fbCamp['id']],
                        [
                            'ad_account_id' => $account->id,
                            'name' => $fbCamp['name'] ?? 'Unknown',
                            'objective' => $fbCamp['objective'] ?? '',
                            'status' => $fbCamp['status'] ?? 'PAUSED',
                            'buying_type' => $fbCamp['buying_type'] ?? 'AUCTION',
                            'daily_budget' => (int) ($fbCamp['daily_budget'] ?? 0) / 100,
                            'lifetime_budget' => (int) ($fbCamp['lifetime_budget'] ?? 0) / 100,
                            'bid_strategy' => $fbCamp['bid_strategy'] ?? 'LOWEST_COST_WITHOUT_CAP',
                            'start_time' => $fbCamp['start_time'] ?? null,
                            'stop_time' => $fbCamp['stop_time'] ?? null,
                            'last_synced_at' => now(),
                        ]
                    );
                    $count++;
                }

                $account->update(['last_synced_at' => now()]);
                $this->info("  Synced {$count} campaigns from: {$account->name}");
            } catch (\Exception $e) {
                Log::error("Failed to sync Meta account {$account->ad_account_id}", ['error' => $e->getMessage()]);
                $this->error("  Failed: {$account->name} — {$e->getMessage()}");
            }
        }
    }
}
