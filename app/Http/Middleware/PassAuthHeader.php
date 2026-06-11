<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PassAuthHeader
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->bearerToken()) {
            if ($token = $request->header('X-Api-Token')) {
                $request->headers->set('Authorization', 'Bearer ' . $token);
            } elseif ($token = $request->query('api_token')) {
                $request->headers->set('Authorization', 'Bearer ' . $token);
            } elseif ($token = $request->server('HTTP_AUTHORIZATION')) {
                $request->headers->set('Authorization', $token);
            } elseif ($token = $request->server('REDIRECT_HTTP_AUTHORIZATION')) {
                $request->headers->set('Authorization', $token);
            } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $request->headers->set('Authorization', $_SERVER['HTTP_AUTHORIZATION']);
            }
        }

        return $next($request);
    }
}
