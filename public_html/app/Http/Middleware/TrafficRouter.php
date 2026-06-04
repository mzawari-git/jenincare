<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\EventSourcingService;
use Illuminate\Support\Facades\View;

class TrafficRouter
{
    private const BOT_THRESHOLD = 70;

    public function __construct(
        private EventSourcingService $eventSourcing,
    ) {}

    public function handle(Request $request, Closure $next)
    {
        if ($request->is('api/*', '_debugbar/*', 'admin/*')) {
            return $next($request);
        }

        if ($request->method() !== 'GET') {
            return $next($request);
        }

        $botScore = $this->getBotScore($request);

        if ($botScore > self::BOT_THRESHOLD) {
            $request->attributes->set('_safe_page', true);
            $request->attributes->set('_bot_detected', true);

            logger()->info('Safe page served to bot/reviewer', [
                'score' => $botScore,
                'ip' => $request->ip(),
                'ua' => $request->userAgent(),
            ]);
        }

        $request->attributes->set('_bot_score', $botScore);

        $response = $next($request);

        if ($botScore > self::BOT_THRESHOLD) {
            $content = $response->getContent();
            $content = $this->applySafePageTransformations($content, $request);
            $response->setContent($content);
        }

        return $response;
    }

    public function getBotScore(Request $request): int
    {
        $ip = $request->ip();
        $sessionKey = 'bot_score_' . md5($ip . ($request->userAgent() ?? ''));
        $clientScore = (int) session($sessionKey, 0);

        $serverScore = $this->computeServerScore($request);

        return max($clientScore, $serverScore);
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

        if (empty($ua) || strlen($ua) < 20) {
            $score += 20;
        }

        if (!$request->hasHeader('Accept-Language')) {
            $score += 15;
        }

        if ($request->hasHeader('X-Forwarded-For') && count(explode(',', $request->header('X-Forwarded-For'))) > 3) {
            $score += 10;
        }

        if (\App\Models\AdReviewerIp::where('ip_address', $request->ip())->where('active', true)->exists()) {
            $score += 40;
        }

        return min(100, $score);
    }

    private function applySafePageTransformations(string $content, Request $request): string
    {
        $replacements = [
            'اشتري الآن' => 'اعرف المزيد',
            'أضف إلى السلة' => 'عرض المنتج',
            'اشترِ' => 'استعرض',
            'اطلب الآن' => 'تواصل معنا',
            'شراء' => 'استعراض',
            'Buy Now' => 'Learn More',
            'Add to Cart' => 'View Product',
            'Purchase' => 'Explore',
        ];

        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        return $content;
    }
}
