<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TrafficQualityService
{
    private array $config;

    public function __construct()
    {
        $this->config = config('tracking.traffic_quality', []);
    }

    public function score(Request $request): array
    {
        $botScore = $this->getBotScore($request);
        $clickVelocity = $this->getClickVelocity($request);
        $ipReputation = $this->getIpReputation($request);

        $quality = 100;

        $quality -= $botScore * ($this->config['bot_score_weight'] ?? 0.4);
        $quality -= $clickVelocity['penalty'] * ($this->config['click_velocity_weight'] ?? 0.3);
        $quality -= (100 - $ipReputation) * ($this->config['ip_reputation_weight'] ?? 0.3);

        $quality = max(0, min(100, round($quality)));

        $data = [
            'quality_score' => $quality,
            'bot_score' => $botScore,
            'click_velocity' => $clickVelocity,
            'ip_reputation' => $ipReputation,
            'is_low_quality' => $quality < ($this->config['quality_threshold_critical'] ?? 30),
            'is_warning' => $quality < ($this->config['quality_threshold_warning'] ?? 50),
            'checked_at' => now()->toIso8601String(),
        ];

        if ($data['is_low_quality']) {
            Log::warning('Low traffic quality detected', [
                'ip' => $request->ip(),
                'quality_score' => $quality,
                'bot_score' => $botScore,
                'click_velocity' => $clickVelocity,
            ]);
        }

        return $data;
    }

    private function getBotScore(Request $request): int
    {
        $ip = $request->ip();
        $sessionKey = 'bot_score_' . md5($ip . ($request->userAgent() ?? ''));
        $clientBotScore = (int) session($sessionKey, 0);
        $serverScore = $this->computeServerScore($request);
        return max($clientBotScore, $serverScore);
    }

    private function computeServerScore(Request $request): int
    {
        $score = 0;
        $ua = $request->userAgent() ?? '';
        $patterns = $this->config['known_bot_ips'] ?? config('tracking.bot_detection.known_bot_ips', []);
        $increments = config('tracking.bot_detection.server_score_increments', []);

        $botPatterns = config('tracking.bot_detection.bot_user_agent_patterns', []);
        foreach ($botPatterns as $pattern) {
            if (stripos($ua, $pattern) !== false) {
                $score += $increments['ua_pattern'] ?? 25;
                break;
            }
        }

        if (in_array($request->ip(), $patterns)) {
            $score += $increments['known_ip'] ?? 30;
        }

        if (empty($ua) || strlen($ua) < 20) {
            $score += $increments['empty_ua'] ?? 20;
        }

        if (!$request->hasHeader('Accept-Language')) {
            $score += $increments['no_accept_language'] ?? 15;
        }

        return min(100, $score);
    }

    private function getClickVelocity(Request $request): array
    {
        $ip = $request->ip();
        $sessionId = session()->getId();
        $window = $this->config['click_velocity_window_minutes'] ?? 60;
        $maxPerIp = $this->config['max_clicks_per_hour_per_ip'] ?? 10;
        $maxPerSession = $this->config['max_clicks_per_hour_per_session'] ?? 20;

        $ipKey = "click_velocity:ip:{$ip}";
        $sessionKey = "click_velocity:session:{$sessionId}";

        $ipCount = (int) Cache::get($ipKey, 0);
        $sessionCount = (int) Cache::get($sessionKey, 0);

        Cache::increment($ipKey);
        Cache::increment($sessionKey);

        if ($ipCount === 0) {
            Cache::expire($ipKey, $window * 60);
            Cache::expire($sessionKey, $window * 60);
        }

        $ipPenalty = min(100, ($ipCount / $maxPerIp) * 100);
        $sessionPenalty = min(100, ($sessionCount / $maxPerSession) * 100);

        $penalty = max($ipPenalty, $sessionPenalty);

        return [
            'penalty' => round($penalty),
            'ip_clicks' => $ipCount,
            'session_clicks' => $sessionCount,
            'max_per_ip' => $maxPerIp,
            'max_per_session' => $maxPerSession,
        ];
    }

    private function getIpReputation(Request $request): int
    {
        $ip = $request->ip();

        $reputationKey = "ip_reputation:{$ip}";
        $cached = Cache::get($reputationKey);
        if ($cached !== null) {
            return (int) $cached;
        }

        $score = 100;

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            $score -= 20;
        }

        $blocked = \App\Models\AdReviewerIp::where('ip_address', $ip)->where('is_blocked', true)->exists();
        if ($blocked) {
            $score -= 50;
        }

        Cache::put($reputationKey, $score, now()->addDay());

        return $score;
    }
}
