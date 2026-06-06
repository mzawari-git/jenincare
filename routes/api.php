<?php

use Illuminate\Support\Facades\Route;
use Modules\CustomAdmin\Http\Controllers\MetaWebhookController;
use App\Http\Controllers\Api\V1\RealtimeController;
use App\Http\Controllers\Admin\Api\AIProviderController;
use App\Http\Controllers\Admin\Api\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\Api\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\Api\ScanManagementController;
use App\Http\Controllers\Admin\Api\UserController as AdminUserController;
use App\Http\Controllers\Admin\Api\WhiteLabelController;
use App\Http\Controllers\Admin\Api\PromptController;
use App\Http\Controllers\Admin\Api\ProductController as AdminProductController;
use App\Http\Controllers\Admin\Api\SettingsController;
use App\Http\Controllers\Api\ReportExportController;
use App\Http\Controllers\Api\SpinCodeController;
use App\Http\Controllers\Api\FingerprintController;
use App\Http\Controllers\Api\BehavioralController;
use App\Http\Controllers\Api\PosBridgeController;
use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\V1\AppConfigController;
use App\Http\Controllers\Api\V1\AppUpdateController;
use App\Http\Controllers\Api\V1\AuthController as V1AuthController;
use App\Http\Controllers\Api\V1\DeviceController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ScanController;
use App\Http\Controllers\Webhook\ShopifyController;
use App\Http\Controllers\Webhook\WooCommerceController;

if (app()->environment('local', 'testing')) {
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
}

Route::prefix('v1')->group(function () {

    Route::get('/app-config', [AppConfigController::class, 'index']);
    Route::get('/app-update', [AppUpdateController::class, 'check']);
    Route::get('/app-update/download', [AppUpdateController::class, 'download']);

    Route::prefix('auth')->group(function () {
        Route::post('/login', [V1AuthController::class, 'login']);
        Route::post('/register', [V1AuthController::class, 'register']);
    });

    Route::post('/device/register', [DeviceController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/profile', [V1AuthController::class, 'profile']);
        Route::put('/profile', [V1AuthController::class, 'updateProfile']);
        Route::post('/logout', [V1AuthController::class, 'logout']);

        Route::post('/device/fcm', [DeviceController::class, 'updateFcm']);

        Route::get('/scans', [ScanController::class, 'index']);
        Route::get('/scans/history', [ScanController::class, 'history']);
        Route::post('/scans/upload', [ScanController::class, 'upload'])
            ->middleware('throttle:scan-upload');
        Route::post('/scans/upload/with-progress', [ScanController::class, 'uploadWithMetadata'])
            ->middleware('throttle:scan-upload');
        Route::post('/scans/upload/chunk', [ScanController::class, 'uploadChunk'])
            ->middleware('throttle:scan-upload');
        Route::get('/scans/{scanId}/status', [ScanController::class, 'status']);
        Route::post('/scans/{scanId}/unlock', [ScanController::class, 'unlock']);
        Route::get('/scans/{scanId}/report', [ScanController::class, 'report']);
        Route::get('/scans/{scanId}/timeline', [ScanController::class, 'timeline']);
        Route::get('/scans/{id}', [ScanController::class, 'show']);

        Route::get('/products/recommended/{scanId}', [ProductController::class, 'recommended']);

        Route::get('/scans/{scanId}/report-qr', [ReportExportController::class, 'generateQr']);
        Route::post('/scans/{scanId}/add-to-cart', function (\Illuminate\Http\Request $request, string $scanId) {
            $service = app(\App\Services\CartIntegrationService::class);
            return response()->json($service->addRecommendedProducts($scanId, $request->user()->id));
        })->name('api.scans.add-to-cart');
    });
});

Route::get('/report/{token}', [ReportExportController::class, 'viewReport'])->name('api.report.view');

Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'time' => now()]);
})->name('api.health');

Route::any('/meta/webhook', [MetaWebhookController::class, 'receiveWebhook'])->name('api.meta.webhook');

Route::prefix('webhooks')->group(function () {
    Route::post('/shopify/{topic}', [ShopifyController::class, 'handle'])
        ->name('webhook.shopify')
        ->where('topic', '.*');

    Route::post('/woocommerce', [WooCommerceController::class, 'handle'])
        ->name('webhook.woocommerce');
});

Route::post('/pos/sale', [PosBridgeController::class, 'store'])->name('api.pos.sale');
Route::get('/pos/stats', [PosBridgeController::class, 'stats'])->name('api.pos.stats');

Route::post('/track/behavior', [BehavioralController::class, 'store'])->name('api.behavioral');
Route::get('/track/behavior/score', [BehavioralController::class, 'score'])->name('api.behavioral.score');

Route::prefix('admin')->group(function () {

    Route::post('/auth/login', [AdminAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'admin'])->group(function () {

        Route::get('/auth/me', [AdminAuthController::class, 'me']);
        Route::post('/auth/logout', [AdminAuthController::class, 'logout']);
        Route::put('/auth/profile', [AdminAuthController::class, 'updateProfile']);

        Route::get('/realtime/stream', [RealtimeController::class, 'stream']);
        Route::get('/realtime/stats', [RealtimeController::class, 'stats']);
        Route::get('/realtime/trends', [RealtimeController::class, 'trends']);

        Route::get('/dashboard/stats', [AdminDashboardController::class, 'stats']);
        Route::get('/dashboard/recent-scans', [AdminDashboardController::class, 'recentScans']);
        Route::get('/dashboard/charts', [AdminDashboardController::class, 'charts']);
        Route::get('/dashboard/quota-usage', [AdminDashboardController::class, 'quotaUsage']);

        Route::get('/scans/stats', [ScanManagementController::class, 'stats']);
        Route::get('/scans/stream', [ScanManagementController::class, 'stream']);
        Route::get('/scans/export', [ScanManagementController::class, 'export']);
        Route::get('/scans/pending', [ScanManagementController::class, 'pending']);
        Route::get('/scans/all', [ScanManagementController::class, 'allScans']);
        Route::get('/scans/pinned', [ScanManagementController::class, 'pinnedScans']);
        Route::get('/scans', [ScanManagementController::class, 'index']);
        Route::get('/scans/{id}', [ScanManagementController::class, 'show']);
        Route::post('/scans/batch-approve', [ScanManagementController::class, 'batchApprove']);
        Route::post('/scans/{id}/approve', [ScanManagementController::class, 'approve']);
        Route::post('/scans/{id}/reject', [ScanManagementController::class, 'reject']);
        Route::post('/scans/{id}/pin', [ScanManagementController::class, 'generatePin']);
        Route::post('/scans/{id}/broadcast', [ScanManagementController::class, 'broadcast']);
        Route::post('/scans/{id}/pin-scan', [ScanManagementController::class, 'pinScan']);
        Route::delete('/scans/{id}/pin-scan', [ScanManagementController::class, 'unpinScan']);
        Route::delete('/scans/{id}', [ScanManagementController::class, 'deleteScan']);

        Route::get('/users', [AdminUserController::class, 'index']);
        Route::get('/users/{id}', [AdminUserController::class, 'show']);
        Route::post('/users', [AdminUserController::class, 'store']);
        Route::put('/users/{id}', [AdminUserController::class, 'update']);
        Route::post('/users/{id}/toggle-active', [AdminUserController::class, 'toggleActive']);

        Route::get('/ai-providers', [AIProviderController::class, 'index']);
        Route::post('/ai-providers', [AIProviderController::class, 'store']);
        Route::get('/ai-providers/quota-status', [AIProviderController::class, 'quotaStatus']);
        Route::get('/ai-providers/{id}', [AIProviderController::class, 'show']);
        Route::put('/ai-providers/{id}', [AIProviderController::class, 'update']);
        Route::post('/ai-providers/{id}/toggle', [AIProviderController::class, 'toggle']);
        Route::post('/ai-providers/{id}/test', [AIProviderController::class, 'testConnection']);

        Route::get('/white-label', [WhiteLabelController::class, 'index']);
        Route::put('/white-label', [WhiteLabelController::class, 'update']);
        Route::post('/white-label/logo', [WhiteLabelController::class, 'uploadLogo']);

        Route::get('/prompts', [PromptController::class, 'index']);
        Route::post('/prompts', [PromptController::class, 'store']);
        Route::post('/prompts/preview', [PromptController::class, 'preview']);
        Route::get('/prompts/{id}', [PromptController::class, 'show']);
        Route::put('/prompts/{id}', [PromptController::class, 'update']);
        Route::delete('/prompts/{id}', [PromptController::class, 'destroy']);
        Route::get('/prompts/{id}/history', [PromptController::class, 'history']);

        Route::get('/settings/skinanalyzer', [SettingsController::class, 'getSkinAnalyzer']);
        Route::post('/settings/skinanalyzer', [SettingsController::class, 'updateSkinAnalyzer']);
        Route::post('/settings/clear-cache', [SettingsController::class, 'clearCache']);

        Route::get('/spin-codes', [SpinCodeController::class, 'index']);
        Route::post('/spin-codes/generate', [SpinCodeController::class, 'generate']);
        Route::get('/spin-codes/{id}', [SpinCodeController::class, 'show']);

        Route::get('/products', [AdminProductController::class, 'index']);
        Route::get('/products/recommendation-rules', [AdminProductController::class, 'recommendationRules']);
        Route::put('/products/recommendation-rules', [AdminProductController::class, 'updateRecommendationRules']);
        Route::post('/products', [AdminProductController::class, 'store']);
        Route::get('/products/{id}', [AdminProductController::class, 'show']);
        Route::put('/products/{id}', [AdminProductController::class, 'update']);
        Route::delete('/products/{id}', [AdminProductController::class, 'destroy']);
    });
});

Route::post('/spin-codes/validate', [SpinCodeController::class, 'validateCode']);
Route::post('/spin-codes/mark-used', [SpinCodeController::class, 'markUsed']);
Route::post('/spin-codes/generate-for-order/{orderId}', [SpinCodeController::class, 'generateForOrder']);

Route::post('/track/fingerprint', [FingerprintController::class, 'store'])->name('api.fingerprint');

Route::prefix('tracking')->group(function () {
    Route::post('/event', [TrackingController::class, 'track'])->name('api.tracking.event');
    Route::post('/batch', [TrackingController::class, 'batch'])->name('api.tracking.batch');
    Route::get('/health', [TrackingController::class, 'health'])->name('api.tracking.health');
});
