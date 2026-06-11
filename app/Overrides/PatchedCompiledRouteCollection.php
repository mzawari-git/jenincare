<?php

namespace App\Overrides;

use Illuminate\Http\Request;
use Illuminate\Routing\CompiledRouteCollection;
use Illuminate\Routing\RouteCollection;
use Symfony\Component\Routing\Matcher\CompiledUrlMatcher;
use Symfony\Component\Routing\RequestContext;

class PatchedCompiledRouteCollection extends CompiledRouteCollection
{
    public function match(Request $request)
    {
        $matcher = new CompiledUrlMatcher(
            $this->compiled,
            (new RequestContext)->fromRequest(
                $trimmedRequest = $this->requestWithoutTrailingSlash($request)
            )
        );

        $route = null;

        try {
            if ($result = $matcher->matchRequest($trimmedRequest)) {
                $route = $this->getByName($result['_route']);
            }
        } catch (\Symfony\Component\Routing\Exception\ResourceNotFoundException|\Symfony\Component\Routing\Exception\MethodNotAllowedException) {
            try {
                return $this->routes->match($request);
            } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                //
            }
        }

        if ($route && $route->isFallback) {
            try {
                $dynamicRoute = $this->routes->match($request);

                if (! $dynamicRoute->isFallback) {
                    $route = $dynamicRoute;
                }
            } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException|\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
                //
            }
        }

        return $this->handleMatchedRoute($request, $route);
    }

    protected function requestWithoutTrailingSlash(Request $request)
    {
        $trimmedRequest = $request->duplicate();

        $parts = explode('?', $request->server->get('REQUEST_URI'), 2);
        $newUri = rtrim($parts[0], '/') . (isset($parts[1]) ? '?' . $parts[1] : '');

        $trimmedRequest->server->set('REQUEST_URI', $newUri);

        // Fix: Also adjust SCRIPT_NAME so the base URL is preserved in the trimmed request.
        // When the trailing slash is removed from REQUEST_URI, base URL detection fails
        // because SCRIPT_NAME's directory (with trailing /) is no longer a prefix.
        // Setting SCRIPT_NAME to the bare directory (without index.php) allows
        // the fallback logic in prepareBaseUrl() to work correctly.
        $baseUrl = $request->getBaseUrl();
        if ($baseUrl !== '') {
            $trimmedRequest->server->set('SCRIPT_NAME', $baseUrl . '/');
        }

        return $trimmedRequest;
    }
}
