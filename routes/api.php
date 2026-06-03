<?php

use Illuminate\Support\Facades\Route;
use Modules\CustomAdmin\Http\Controllers\MetaWebhookController;

/*
|--------------------------------------------------------------------------
| Mobile App API v1 - SkinAnalyzer
|--------------------------------------------------------------------------
*/
Route::get('/setup-token', function () {
    $user = \App\Models\User::firstOrCreate(
        ['email' => 'default@jenincare.com'],
        ['name' => 'Default User', 'password' => bcrypt('password123'), 'phone' => '0500000000']
    );
    $user->tokens()->delete();
    $token = $user->createToken('default-token', ['*']);
    return response()->json([
        'token' => $token->plainTextToken,
        'user_id' => $user->id,
    ]);
});

Route::prefix('v1')->group(function () {

    // Public endpoints
    Route::get('/app-config', [\App\Http\Controllers\Api\V1\AppConfigController::class, 'index']);
    Route::get('/app-update', [\App\Http\Controllers\Api\V1\AppUpdateController::class, 'check']);
    Route::get('/app-update/download', [\App\Http\Controllers\Api\V1\AppUpdateController::class, 'download']);

    Route::prefix('auth')->group(function () {
        Route::post('/login', [\App\Http\Controllers\Api\V1\AuthController::class, 'login']);
        Route::post('/register', [\App\Http\Controllers\Api\V1\AuthController::class, 'register']);
    });

    // Device registration (public, associates with user if authenticated)
    Route::post('/device/register', [\App\Http\Controllers\Api\V1\DeviceController::class, 'register']);

    // Authenticated endpoints
    Route::middleware('auth:sanctum')->group(function () {

        // Profile
        Route::get('/profile', [\App\Http\Controllers\Api\V1\AuthController::class, 'profile']);
        Route::put('/profile', [\App\Http\Controllers\Api\V1\AuthController::class, 'updateProfile']);
        Route::post('/logout', [\App\Http\Controllers\Api\V1\AuthController::class, 'logout']);

        // Device
        Route::post('/device/fcm', [\App\Http\Controllers\Api\V1\DeviceController::class, 'updateFcm']);

        // Scans
        Route::get('/scans', [\App\Http\Controllers\Api\V1\ScanController::class, 'index']);
        Route::get('/scans/history', [\App\Http\Controllers\Api\V1\ScanController::class, 'history']);
        Route::post('/scans/upload', [\App\Http\Controllers\Api\V1\ScanController::class, 'upload']);
        Route::post('/scans/upload/with-progress', [\App\Http\Controllers\Api\V1\ScanController::class, 'uploadWithMetadata']);
        Route::post('/scans/upload/chunk', [\App\Http\Controllers\Api\V1\ScanController::class, 'uploadChunk']);
        Route::get('/scans/{scanId}/status', [\App\Http\Controllers\Api\V1\ScanController::class, 'status']);
        Route::post('/scans/{scanId}/unlock', [\App\Http\Controllers\Api\V1\ScanController::class, 'unlock']);
        Route::get('/scans/{scanId}/report', [\App\Http\Controllers\Api\V1\ScanController::class, 'report']);
        Route::get('/scans/{scanId}/timeline', [\App\Http\Controllers\Api\V1\ScanController::class, 'timeline']);
        Route::get('/scans/{id}', [\App\Http\Controllers\Api\V1\ScanController::class, 'show']);

        // Products
        Route::get('/products/recommended/{scanId}', [\App\Http\Controllers\Api\V1\ProductController::class, 'recommended']);
    });
});

// Existing API routes
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'time' => now()]);
});

Route::any('/meta/webhook', [MetaWebhookController::class, 'receiveWebhook'])->name('api.meta.webhook');

Route::prefix('webhooks')->group(function () {
    Route::post('/shopify/{topic}', [\App\Http\Controllers\Webhook\ShopifyController::class, 'handle'])
        ->name('webhook.shopify')
        ->where('topic', '.*');

    Route::post('/woocommerce', [\App\Http\Controllers\Webhook\WooCommerceController::class, 'handle'])
        ->name('webhook.woocommerce');
});

Route::post('/pos/sale', [\App\Http\Controllers\Api\PosBridgeController::class, 'store'])
    ->name('api.pos.sale');
Route::get('/pos/stats', [\App\Http\Controllers\Api\PosBridgeController::class, 'stats'])
    ->name('api.pos.stats');

Route::post('/track/behavior', [\App\Http\Controllers\Api\BehavioralController::class, 'store'])
    ->name('api.behavioral');
Route::get('/track/behavior/score', [\App\Http\Controllers\Api\BehavioralController::class, 'score'])
    ->name('api.behavioral.score');

// Admin API - SkinAnalyzer Management
use App\Http\Controllers\Admin\Api\AIProviderController;
use App\Http\Controllers\Admin\Api\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\Api\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\Api\ScanManagementController;
use App\Http\Controllers\Admin\Api\UserController as AdminUserController;

Route::prefix('admin')->group(function () {

    // Auth (public)
    Route::post('/auth/login', [AdminAuthController::class, 'login']);

    // Authenticated admin routes
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        // Auth
        Route::get('/auth/me', [AdminAuthController::class, 'me']);
        Route::post('/auth/logout', [AdminAuthController::class, 'logout']);
        Route::put('/auth/profile', [AdminAuthController::class, 'updateProfile']);

        // Dashboard
        Route::get('/dashboard/stats', [AdminDashboardController::class, 'stats']);
        Route::get('/dashboard/recent-scans', [AdminDashboardController::class, 'recentScans']);
        Route::get('/dashboard/charts', [AdminDashboardController::class, 'charts']);
        Route::get('/dashboard/quota-usage', [AdminDashboardController::class, 'quotaUsage']);

        // Scans
        Route::get('/scans/stats', [ScanManagementController::class, 'stats']);
        Route::get('/scans/export', [ScanManagementController::class, 'export']);
        Route::get('/scans/pending', [ScanManagementController::class, 'pending']);
        Route::get('/scans/all', [ScanManagementController::class, 'allScans']);
        Route::post('/scans/batch-approve', [ScanManagementController::class, 'batchApprove']);
        Route::post('/scans/{id}/approve', [ScanManagementController::class, 'approve']);
        Route::post('/scans/{id}/reject', [ScanManagementController::class, 'reject']);
        Route::post('/scans/{id}/pin', [ScanManagementController::class, 'generatePin']);
        Route::post('/scans/{id}/broadcast', [ScanManagementController::class, 'broadcast']);
        Route::post('/scans/{id}/pin-scan', [ScanManagementController::class, 'pinScan']);
        Route::delete('/scans/{id}/pin-scan', [ScanManagementController::class, 'unpinScan']);
        Route::get('/scans/pinned', [ScanManagementController::class, 'pinnedScans']);
        Route::get('/scans/{id}', [ScanManagementController::class, 'show']);
        Route::get('/scans', [ScanManagementController::class, 'index']);

        // Users
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::get('/users/{id}', [AdminUserController::class, 'show']);
        Route::post('/users', [AdminUserController::class, 'store']);
        Route::put('/users/{id}', [AdminUserController::class, 'update']);
        Route::post('/users/{id}/toggle-active', [AdminUserController::class, 'toggleActive']);

        // AI Providers
        Route::get('/ai-providers', [AIProviderController::class, 'index']);
        Route::post('/ai-providers', [AIProviderController::class, 'store']);
        Route::get('/ai-providers/quota-status', [AIProviderController::class, 'quotaStatus']);
        Route::get('/ai-providers/{id}', [AIProviderController::class, 'show']);
        Route::put('/ai-providers/{id}', [AIProviderController::class, 'update']);
        Route::post('/ai-providers/{id}/toggle', [AIProviderController::class, 'toggle']);
        Route::post('/ai-providers/{id}/test', [AIProviderController::class, 'testConnection']);
    });
});

Route::post('/track/fingerprint', [\App\Http\Controllers\Api\FingerprintController::class, 'store'])
    ->name('api.fingerprint');

Route::prefix('tracking')->group(function () {
    Route::post('/event', [\App\Http\Controllers\Api\TrackingController::class, 'track'])
        ->name('api.tracking.event');

    Route::post('/batch', [\App\Http\Controllers\Api\TrackingController::class, 'batch'])
        ->name('api.tracking.batch');

    Route::get('/health', [\App\Http\Controllers\Api\TrackingController::class, 'health'])
        ->name('api.tracking.health');
});
