<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TokenManagerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SocialAuthController extends Controller
{
    public function __construct(
        private TokenManagerService $tokens,
    ) {}

    public function redirect(string $platform, Request $request)
    {
        $config = config("oauth.{$platform}");

        if (!$config || empty($config['client_id'])) {
            return redirect()->route('admin.ads.dashboard')
                ->with('error', "{$platform} OAuth is not configured. Please set credentials.");
        }

        $state = $this->tokens->generateState($platform);
        $codeVerifier = null;

        $params = [
            'client_id' => $config['client_id'],
            'redirect_uri' => url($config['redirect']),
            'response_type' => 'code',
            'scope' => is_array($config['scopes']) ? implode(',', $config['scopes']) : $config['scopes'],
            'state' => $state,
            'access_type' => 'offline',
            'prompt' => 'consent',
        ];

        if (!empty($config['use_pkce'])) {
            $codeVerifier = $this->tokens->generateCodeVerifier();
            $params['code_challenge'] = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
            $params['code_challenge_method'] = 'S256';
        }

        return redirect()->away($config['auth_url'] . '?' . http_build_query($params));
    }

    public function callback(string $platform, Request $request)
    {
        $config = config("oauth.{$platform}");

        if (!$config) {
            return redirect()->route('admin.ads.dashboard')->with('error', "Unknown platform: {$platform}");
        }

        $code = $request->input('code');
        $state = $request->input('state');
        $error = $request->input('error');

        if ($error) {
            Log::warning("OAuth error from {$platform}: {$error}", ['description' => $request->input('error_description')]);
            return redirect()->route('admin.ads.dashboard')->with('error', "Platform returned error: {$error}");
        }

        if (!$code) {
            return redirect()->route('admin.ads.dashboard')->with('error', "No authorization code received from {$platform}.");
        }

        if (!$this->tokens->validateState($platform, $state)) {
            return redirect()->route('admin.ads.dashboard')->with('error', "Invalid state parameter — possible CSRF attack.");
        }

        $tokenData = $this->exchangeCode($platform, $config, $code);
        if (!$tokenData) {
            return redirect()->route('admin.ads.dashboard')->with('error', "Failed to exchange code for token with {$config['name']}.");
        }

        $this->tokens->store(
            $platform,
            $tokenData['access_token'],
            $tokenData['refresh_token'] ?? null,
            $tokenData['expires_in'] ?? null,
            ['platform' => $platform, 'scopes' => $config['scopes'] ?? []]
        );

        $this->onPlatformConnected($platform, $config, $tokenData['access_token']);

        return redirect()->route('admin.ads.dashboard')
            ->with('success', "{$config['name']} connected! Ad accounts imported. Campaigns synced automatically.");
    }

    private function exchangeCode(string $platform, array $config, string $code): ?array
    {
        try {
            $params = [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'redirect_uri' => url($config['redirect']),
            ];

            if (!empty($config['use_pkce'])) {
                $params['code_verifier'] = $this->tokens->getCodeVerifier();
            }

            $response = Http::asForm()->post($config['token_url'], $params);

            if ($response->successful()) {
                $data = $response->json();
                Log::info("OAuth token received for {$config['name']}", [
                    'has_refresh' => !empty($data['refresh_token']),
                    'expires_in' => $data['expires_in'] ?? 'unknown',
                ]);
                return $data;
            }

            Log::error("OAuth token exchange failed for {$config['name']}", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error("OAuth token exchange error for {$config['name']}", [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function onPlatformConnected(string $platform, array $config, string $token): void
    {
        if ($platform === 'meta') {
            try {
                $graph = app(\App\Services\Meta\FacebookGraphService::class);
                $graph->setUserAccessToken($token);
                $debug = $graph->debugToken($token);

                if ($debug) {
                    $longLived = $graph->exchangeToken($token);
                    if ($longLived && !empty($longLived['access_token'])) {
                        $this->tokens->store(
                            'meta',
                            $longLived['access_token'],
                            null,
                            $longLived['expires_in'] ?? 5184000,
                            ['platform' => 'meta']
                        );

                        $adAccounts = $graph->getAdAccounts($longLived['access_token']);
                        if (!empty($adAccounts)) {
                            $imported = 0;
                            foreach ($adAccounts as $acc) {
                                $adAccountId = str_replace('act_', '', $acc['id'] ?? $acc['account_id'] ?? '');
                                if ($adAccountId) {
                                    \App\Models\Meta\MetaAdAccount::updateOrCreate(
                                        ['ad_account_id' => $adAccountId],
                                        [
                                            'name' => $acc['name'] ?? 'Unnamed',
                                            'currency' => $acc['currency'] ?? 'ILS',
                                            'timezone' => $acc['timezone_name'] ?? 'Asia/Jerusalem',
                                            'access_token' => $longLived['access_token'],
                                            'business_id' => $acc['business_id'] ?? null,
                                            'spend_cap' => ($acc['spend_cap'] ?? 0) / 100,
                                            'amount_spent' => ($acc['amount_spent'] ?? 0) / 100,
                                            'account_status' => 'active',
                                            'is_active' => true,
                                            'last_synced_at' => now(),
                                        ]
                                    );
                                    $imported++;
                                }
                            }
                            Log::info("Imported {$imported} Meta ad accounts");

                            foreach (\App\Models\Meta\MetaAdAccount::where('is_active', true)->get() as $account) {
                                try {
                                    $graph->setUserAccessToken($account->access_token);
                                    $campaigns = $graph->getCampaigns($account->ad_account_id);
                                    if (!empty($campaigns)) {
                                        $synced = 0;
                                        foreach ($campaigns as $fbCamp) {
                                            \App\Models\Meta\MetaCampaign::updateOrCreate(
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
                                            $synced++;
                                        }
                                        $account->update(['last_synced_at' => now()]);
                                        Log::info("Auto-synced {$synced} campaigns for account {$account->name}");
                                    }
                                } catch (\Exception $e) {
                                    Log::warning("Campaign sync skipped for {$account->name}: " . $e->getMessage());
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Meta auto-import failed after OAuth', ['error' => $e->getMessage()]);
            }
        }

        if ($platform === 'shopify') {
            $shop = request()->input('shop');
            if ($shop) {
                \App\Models\MarketingSetting::set('shopify_shop_domain', $shop);
                \App\Models\MarketingSetting::set('shopify_enabled', true);
            }
        }

        \App\Models\MarketingSetting::set("{$platform}_connected", true);
        \App\Models\MarketingSetting::set("{$platform}_connected_at", now()->toIso8601String());
    }

    public function disconnect(string $platform)
    {
        $config = config("oauth.{$platform}");
        $name = $config['name'] ?? $platform;
        $this->tokens->disconnect($platform);

        \App\Models\MarketingSetting::set("{$platform}_connected", false);

        return redirect()->route('admin.ads.dashboard')
            ->with('success', "{$name} account disconnected.");
    }
}
