<?php

namespace App\Services;

use App\Models\MarketingSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TokenManagerService
{
    private string $prefix = 'oauth_token_';

    public function store(string $platform, string $token, ?string $refreshToken = null, ?int $expiresIn = null, array $meta = []): void
    {
        $payload = [
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'expires_at' => $expiresIn ? now()->addSeconds($expiresIn)->timestamp : null,
            'meta' => $meta,
            'stored_at' => now()->toIso8601String(),
        ];

        $encrypted = Crypt::encryptString(json_encode($payload));

        MarketingSetting::set("{$this->prefix}{$platform}", $encrypted);
        MarketingSetting::set("{$this->prefix}{$platform}_connected", true);
        MarketingSetting::set("{$this->prefix}{$platform}_connected_at", now()->toIso8601String());

        Log::info("OAuth token stored for {$platform}", ['meta' => array_keys($meta)]);
    }

    public function get(string $platform): ?array
    {
        $encrypted = MarketingSetting::get("{$this->prefix}{$platform}");

        if (!$encrypted) {
            return null;
        }

        try {
            $payload = json_decode(Crypt::decryptString($encrypted), true);

            if (!empty($payload['expires_at']) && $payload['expires_at'] < now()->timestamp) {
                Log::info("OAuth token expired for {$platform}", [
                    'expired_at' => date('Y-m-d H:i:s', $payload['expires_at']),
                ]);

                if (!empty($payload['refresh_token'])) {
                    return $this->refresh($platform, $payload);
                }

                return null;
            }

            return $payload;
        } catch (\Exception $e) {
            Log::error("Failed to decrypt token for {$platform}", ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function getToken(string $platform): ?string
    {
        $data = $this->get($platform);
        return $data['access_token'] ?? null;
    }

    public function refresh(string $platform, ?array $current = null): ?array
    {
        $config = config("oauth.{$platform}");
        if (!$config || empty($config['refresh_url'])) {
            return null;
        }

        $data = $current ?? $this->get($platform);
        if (!$data || empty($data['refresh_token'])) {
            return null;
        }

        try {
            $params = [
                'grant_type' => $config['refresh_grant'] ?? 'refresh_token',
                'refresh_token' => $data['refresh_token'],
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
            ];

            $response = Http::asForm()->post($config['refresh_url'], $params);

            if ($response->successful()) {
                $body = $response->json();
                $newToken = $body['access_token'] ?? null;

                if ($newToken) {
                    $this->store(
                        $platform,
                        $newToken,
                        $body['refresh_token'] ?? $data['refresh_token'],
                        $body['expires_in'] ?? null,
                        $data['meta'] ?? []
                    );

                    Log::info("OAuth token refreshed for {$platform}");
                    return $this->get($platform);
                }
            }

            Log::warning("OAuth token refresh failed for {$platform}", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error("OAuth token refresh error for {$platform}", ['error' => $e->getMessage()]);
        }

        return null;
    }

    public function isConnected(string $platform): bool
    {
        return (bool) MarketingSetting::get("{$this->prefix}{$platform}_connected", false);
    }

    public function getConnectedAt(string $platform): ?string
    {
        return MarketingSetting::get("{$this->prefix}{$platform}_connected_at");
    }

    public function disconnect(string $platform): void
    {
        MarketingSetting::set("{$this->prefix}{$platform}", null);
        MarketingSetting::set("{$this->prefix}{$platform}_connected", false);
        MarketingSetting::set("{$this->prefix}{$platform}_connected_at", null);

        Log::info("OAuth token removed for {$platform}");
    }

    public function getAllConnected(): array
    {
        $connected = [];
        $platforms = array_keys(config('oauth', []));

        foreach ($platforms as $platform) {
            if ($this->isConnected($platform)) {
                $connected[$platform] = [
                    'connected_at' => $this->getConnectedAt($platform),
                    'config' => config("oauth.{$platform}", []),
                ];
            }
        }

        return $connected;
    }

    public function generateState(string $platform): string
    {
        $state = bin2hex(random_bytes(16));
        session()->put("oauth_state_{$platform}", $state);
        session()->put("oauth_state_{$platform}_time", now()->timestamp);
        return $state;
    }

    public function validateState(string $platform, string $state): bool
    {
        $stored = session()->pull("oauth_state_{$platform}");
        $time = session()->pull("oauth_state_{$platform}_time");

        if (!$stored || $stored !== $state) {
            return false;
        }

        return ($time && (now()->timestamp - $time) < 600);
    }

    public function generateCodeVerifier(): string
    {
        $verifier = bin2hex(random_bytes(32));
        session()->put('oauth_code_verifier', $verifier);
        return $verifier;
    }

    public function getCodeVerifier(): ?string
    {
        return session()->pull('oauth_code_verifier');
    }
}
