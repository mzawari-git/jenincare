<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ScanApprovalController;
use App\Http\Controllers\Admin\AIProviderController;
use App\Http\Controllers\Admin\PromptController;
use App\Http\Controllers\Admin\WhiteLabelController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
    Route::post('/users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::post('/users/{id}/toggle-active', [\App\Http\Controllers\Admin\UserController::class, 'toggleActive'])->name('users.toggle-active');

    Route::get('/scans/pending', [DashboardController::class, 'pendingScans'])->name('scans.pending');
    Route::get('/scans/all', [DashboardController::class, 'allScans'])->name('scans.all');
    Route::get('/scans/{id}', [DashboardController::class, 'scanDetail'])->name('scans.detail');

    Route::post('/scans/{id}/approve', [ScanApprovalController::class, 'approve'])->name('scans.approve');
    Route::post('/scans/{id}/reject', [ScanApprovalController::class, 'reject'])->name('scans.reject');
    Route::post('/scans/{id}/generate-pin', [ScanApprovalController::class, 'generatePin'])->name('scans.generate-pin');
    Route::post('/scans/{id}/broadcast', [ScanApprovalController::class, 'broadcastResult'])->name('scans.broadcast');
    Route::post('/scans/batch/approve', [ScanApprovalController::class, 'batchApprove'])->name('scans.batch-approve');

    Route::get('/providers', [AIProviderController::class, 'index'])->name('providers.index');
    Route::get('/providers/quota-status', [AIProviderController::class, 'quotaStatus'])->name('providers.quota-status');
    Route::get('/providers/{id}', [AIProviderController::class, 'show'])->name('providers.show');
    Route::put('/providers/{id}', [AIProviderController::class, 'update'])->name('providers.update');
    Route::post('/providers/{id}/activate', [AIProviderController::class, 'activate'])->name('providers.activate');
    Route::post('/providers/{id}/deactivate', [AIProviderController::class, 'deactivate'])->name('providers.deactivate');
    Route::post('/providers/{id}/test-connection', [AIProviderController::class, 'testConnection'])->name('providers.test-connection');

    Route::get('/prompts', [PromptController::class, 'index'])->name('prompts.index');
    Route::get('/prompts/variables', [PromptController::class, 'variables'])->name('prompts.variables');
    Route::post('/prompts', [PromptController::class, 'store'])->name('prompts.store');
    Route::get('/prompts/{id}', [PromptController::class, 'show'])->name('prompts.show');
    Route::put('/prompts/{id}', [PromptController::class, 'update'])->name('prompts.update');

    Route::get('/white-label', [WhiteLabelController::class, 'show'])->name('white-label.show');
    Route::put('/white-label', [WhiteLabelController::class, 'update'])->name('white-label.update');
    Route::post('/white-label/logo', [WhiteLabelController::class, 'uploadLogo'])->name('white-label.upload-logo');
    Route::get('/white-label/preview', [WhiteLabelController::class, 'preview'])->name('white-label.preview');
});
