<?php

use Illuminate\Support\Facades\Route;
use Modules\CustomAdmin\Http\Controllers\MetaWebhookController;

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
