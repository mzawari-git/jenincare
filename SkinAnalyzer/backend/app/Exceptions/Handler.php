<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $levels = [];

    protected $dontReport = [];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->renderApiException($e);
        }

        return parent::render($request, $e);
    }

    private function renderApiException(Throwable $e): JsonResponse
    {
        if ($e instanceof ValidationException) {
            return response()->json([
                'message' => __('validation.failed'),
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($e instanceof AuthenticationException) {
            return response()->json([
                'message' => __('auth.unauthenticated'),
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($e instanceof AccessDeniedHttpException) {
            return response()->json([
                'message' => __('auth.forbidden'),
            ], Response::HTTP_FORBIDDEN);
        }

        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return response()->json([
                'message' => __('resource.not_found'),
            ], Response::HTTP_NOT_FOUND);
        }

        if ($e instanceof ThrottleRequestsException) {
            return response()->json([
                'message' => __('auth.throttle'),
                'retry_after' => $e->getHeaders()['Retry-After'] ?? null,
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        $statusCode = $this->isHttpException($e) ? $e->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;

        return response()->json([
            'message' => __('server.error'),
            'exception' => config('app.debug') ? $e->getMessage() : null,
        ], $statusCode);
    }
}
