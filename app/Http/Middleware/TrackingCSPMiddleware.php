<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TrackingCSPMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' " .
               "https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.tailwindcss.com https://unpkg.com " .
               "https://connect.facebook.net https://analytics.tiktok.com https://www.googletagmanager.com " .
               "https://static.ads-twitter.com https://snap.licdn.com https://s.pinimg.com https://ct.pinterest.com " .
               "https://tr.snapchat.com https://sc-static.net; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.tailwindcss.com; " .
               "img-src 'self' data: https: http:; " .
               "connect-src 'self' " .
               "https://www.facebook.com https://graph.facebook.com https://analytics.tiktok.com " .
               "https://www.googleadservices.com https://googleads.g.doubleclick.net " .
               "https://ct.pinterest.com https://tr.snapchat.com https://snap.licdn.com " .
               "https://static.ads-twitter.com https://analytics.twitter.com " .
               "https://cdn.tailwindcss.com https://unpkg.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
               "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; " .
               "frame-src 'self' https://www.facebook.com https://connect.facebook.net https://www.youtube.com https://player.vimeo.com; " .
               "media-src 'self'; " .
               "object-src 'none'; " .
               "base-uri 'self'; " .
               "form-action 'self';";

        // CSP is already handled by .htaccess at Apache level (more efficient)
        // This prevents double CSP headers that cause conflicts
        // $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
