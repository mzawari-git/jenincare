<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $isAdmin = method_exists($user, 'isAdmin')
            ? $user->isAdmin()
            : (method_exists($user, 'hasRole')
                ? $user->hasRole('admin')
                : ($user->is_admin ?? $user->role === 'admin'));

        if (! $isAdmin) {
            return response()->json(['message' => 'Forbidden. Admin access required.'], 403);
        }

        return $next($request);
    }
}
