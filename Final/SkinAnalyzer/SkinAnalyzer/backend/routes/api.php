<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\AppConfigController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ScanController;
use Illuminate\Support\Facades\Route;

Route::prefix('health')->group(function () {
    Route::get('/', function () {
        return response()->json([
            'status' => 'ok',
            'service' => 'SkinAnalyzer',
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String(),
        ]);
    });
});

Route::prefix('admin/auth')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:auth');
});

Route::prefix('v1')->group(function () {
    Route::get('/app-config', [AppConfigController::class, 'index']);
    Route::get('/app-update', [AppConfigController::class, 'checkAppUpdate']);

    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:auth');
    Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:auth');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/device/register', [AuthController::class, 'deviceRegister']);

        Route::post('/scans', [ScanController::class, 'store']);
        Route::post('/scans/upload', [ScanController::class, 'store']);
        Route::post('/scans/upload/with-progress', [ScanController::class, 'uploadWithProgress']);
        Route::post('/scans/upload/chunk', [ScanController::class, 'uploadChunk']);
        Route::get('/scans', [ScanController::class, 'index']);
        Route::get('/scans/history', [ScanController::class, 'history']);
        Route::get('/scans/{id}', [ScanController::class, 'show']);
        Route::get('/scans/{id}/status', [ScanController::class, 'status']);
        Route::get('/scans/{id}/report', [ScanController::class, 'report']);
        Route::get('/scans/{id}/timeline', [ScanController::class, 'timeline']);
        Route::post('/scans/{id}/unlock', [ScanController::class, 'unlock'])->middleware('throttle:pin');
        Route::post('/scans/{id}/add-to-cart', [ScanController::class, 'addToCart']);

        Route::get('/products/recommended/{scanId}', [ProductController::class, 'recommended']);
        Route::get('/products', [ProductController::class, 'index']);
    });
});
