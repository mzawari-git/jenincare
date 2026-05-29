<?php

namespace App\Http\Middleware;

use App\Services\EventSourcingService;
use App\Services\IdentityService;
use Closure;
use Illuminate\Http\Request;

class UuidMiddleware
{
    public function __construct(
        private IdentityService $identityService,
        private EventSourcingService $eventSourcingService,
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $identity = $this->identityService->getIdentity($request);

        $request->merge(['_identity' => $identity]);
        $request->attributes->set('_identity', $identity);

        $response = $next($request);

        if ($this->shouldRecordPageView($request)) {
            $this->eventSourcingService->recordEvent(
                $identity['uuid'],
                'page_view',
                $identity
            );
        }

        return $response;
    }

    private function shouldRecordPageView(Request $request): bool
    {
        if ($request->is('api/*', '_debugbar/*', 'admin/*')) {
            return false;
        }

        if ($request->ajax() || $request->wantsJson()) {
            return false;
        }

        if ($request->method() !== 'GET') {
            return false;
        }

        return true;
    }
}
