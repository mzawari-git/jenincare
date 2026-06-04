<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class PerformanceOptimization
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Add performance headers
        if ($response instanceof \Illuminate\Http\Response) {
            // Enable compression
            if (!ob_get_level() && !headers_sent()) {
                ob_start('ob_gzhandler');
            }

            // Add performance headers
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
            $response->headers->set('X-XSS-Protection', '1; mode=block');
            
            // Cache control for static assets
            if ($this->isStaticAsset($request)) {
                $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
            } elseif ($request->is('api/*')) {
                $response->headers->set('Cache-Control', 'no-cache, must-revalidate');
            } elseif ($request->route() && $request->route()->named('home')) {
                // Homepage: no browser cache so edits appear immediately
                $response->headers->set('Cache-Control', 'no-cache, must-revalidate');
            } else {
                $response->headers->set('Cache-Control', 'public, max-age=600');
            }

            // Add ETag for better caching
            $etag = md5($response->getContent());
            $response->headers->set('ETag', $etag);
            
            // Check if client has cached version
            if ($request->header('If-None-Match') === $etag) {
                return response('', 304);
            }
        }

        return $response;
    }

    private function isStaticAsset(Request $request): bool
    {
        $path = $request->path();
        $staticExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'ico', 'woff', 'woff2', 'ttf', 'eot'];
        
        foreach ($staticExtensions as $ext) {
            if (str_ends_with($path, '.' . $ext)) {
                return true;
            }
        }
        
        return false;
    }
}
