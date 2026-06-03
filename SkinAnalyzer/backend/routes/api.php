<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AppConfigController;
use App\Http\Controllers\Api\ScanController;
use App\Http\Controllers\Api\ProductController;
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

Route::prefix('v1')->group(function () {
    Route::get('/app-config', [AppConfigController::class, 'index']);

    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/device/register', [AuthController::class, 'deviceRegister']);

        Route::post('/scans', [ScanController::class, 'store']);
        Route::get('/scans', [ScanController::class, 'index']);
        Route::get('/scans/{id}', [ScanController::class, 'show']);
        Route::post('/scans/{id}/unlock', [ScanController::class, 'unlock']);

        Route::get('/products/recommended/{scanId}', [ProductController::class, 'recommended']);
        Route::get('/products', [ProductController::class, 'index']);
    });
});
