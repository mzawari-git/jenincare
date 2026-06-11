<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        $response = $next($request);

        if ($response instanceof Response && ! $this->isAlreadyJson($response)) {
            $content = $response->getContent();

            $decoded = json_decode($content, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'data' => $decoded !== null ? $decoded : $content,
            ], JSON_UNESCAPED_UNICODE));
        }

        return $response;
    }

    private function isAlreadyJson(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');

        return str_contains($contentType, 'application/json')
            || str_contains($contentType, 'application/vnd.api+json');
    }
}
