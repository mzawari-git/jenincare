<?php

namespace App\Http\Middleware;

use App\Helpers\CtaHelper;
use Closure;
use Illuminate\Http\Request;

class DynamicCtaMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $botScore = (int) $request->attributes->get('_bot_score', 0);
        $isBot = (bool) $request->attributes->get('_bot_detected', false);
        $uuid = $request->cookie('_juuid');

        $isNewUser = $uuid ? !cache("user_seen_{$uuid}") : true;

        if ($uuid && $isNewUser) {
            cache(["user_seen_{$uuid}" => true, now()->addDay()]);
        }

        $context = [
            'is_bot' => $isBot,
            'bot_score' => $botScore,
            'is_new_user' => $isNewUser,
            'uuid' => $uuid,
        ];

        $request->attributes->set('_cta_context', $context);
        $request->attributes->set('_cta', function (string $key) use ($context) {
            return CtaHelper::getCta($key, $context);
        });

        return $next($request);
    }
}
