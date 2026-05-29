<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BehavioralController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'bot_score' => 'required|integer|min:0|max:100',
            'time_on_page' => 'nullable|integer',
            'scroll_depth' => 'nullable|integer',
            'click_count' => 'nullable|integer',
            'keypress_count' => 'nullable|integer',
            'mouse_distance' => 'nullable|integer',
            'max_mouse_speed' => 'nullable|integer',
            'scroll_events' => 'nullable|integer',
            'user_agent' => 'nullable|string',
        ]);

        $botScore = $data['bot_score'];

        $ip = $request->ip();
        $sessionKey = 'bot_score_' . md5($ip . ($request->userAgent() ?? ''));

        session([$sessionKey => $botScore]);

        $this->validateBehavior($data);

        return response()->json(['success' => true, 'bot_score' => $botScore]);
    }

    public function score(Request $request)
    {
        $ip = $request->ip();
        $sessionKey = 'bot_score_' . md5($ip . ($request->userAgent() ?? ''));

        $botScore = (int) session($sessionKey, 0);

        $serverScore = $this->computeServerScore($request);

        $finalScore = max($botScore, $serverScore);

        return response()->json([
            'bot_score' => $finalScore,
            'is_bot' => $finalScore > 70,
            'source' => $botScore >= $serverScore ? 'client' : 'server',
        ]);
    }

    private function validateBehavior(array $data): void
    {
        $score = $data['bot_score'];

        if ($score > 70) {
            logger()->warning('High bot score detected', [
                'score' => $score,
                'ip' => request()->ip(),
                'user_agent' => $data['user_agent'] ?? 'unknown',
            ]);
        }
    }

    private function computeServerScore(Request $request): int
    {
        $score = 0;

        $ua = $request->userAgent() ?? '';

        $botPatterns = ['bot', 'crawl', 'spider', 'scrape', 'curl', 'wget', 'python', 'httpclient', 'go-http-client', 'headless', 'phantom', 'selenium', 'puppeteer', 'playwright'];
        foreach ($botPatterns as $pattern) {
            if (stripos($ua, $pattern) !== false) {
                $score += 25;
            }
        }

        $knownBotIPs = config('tracking.bot_detection.known_bot_ips', []);
        if (in_array($request->ip(), $knownBotIPs)) {
            $score += 30;
        }

        if (empty($ua) || strlen($ua) < 20) {
            $score += 20;
        }

        if (!$request->hasHeader('Accept-Language')) {
            $score += 15;
        }

        return min(100, $score);
    }
}
