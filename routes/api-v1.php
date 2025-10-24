<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\TableSuggestionController;
use App\Http\Controllers\Api\SettingController;

/*
|--------------------------------------------------------------------------
| API Routes (v1)
|--------------------------------------------------------------------------
| RESTful API endpoints with rate limiting and standardized responses
*/

Route::middleware(['api', 'throttle:api'])->group(function () {

    // Queue Management
    Route::prefix('queue')->name('queue.')->group(function () {
        Route::get('/stats', [ApiController::class, 'getQueueStats'])->name('stats');
        Route::get('/summary', [ApiController::class, 'getQueueSummary'])->name('summary');
        Route::get('/update', [ApiController::class, 'getQueueUpdate'])->name('update');
        Route::post('/update-wait-times', [ApiController::class, 'updateAllWaitTimes'])->name('update-wait-times');
    });

    // Customer Management
    Route::prefix('customer')->name('customer.')->group(function () {
        Route::get('/{customerId}/current-wait', [ApiController::class, 'getCurrentWait'])->name('current-wait');
        Route::get('/{queueNumber}/position', [ApiController::class, 'getPosition'])->name('position');
        Route::post('/request-verification', [VerificationController::class, 'requestVerification'])->name('request-verification');
        Route::get('/verification-status/{id}', [VerificationController::class, 'checkVerificationStatus'])->name('verification-status');
    });

    // Priority Verification
    Route::prefix('verification')->name('verification.')->group(function () {
        Route::get('/pending', [VerificationController::class, 'getPendingVerifications'])->name('pending');
        Route::get('/completed', [VerificationController::class, 'getCompletedVerifications'])->name('completed');
        Route::post('/complete', [VerificationController::class, 'completeVerification'])->name('complete');
        Route::post('/reject', [VerificationController::class, 'rejectVerification'])->name('reject');
    });

    // Table Management
    Route::prefix('tables')->name('tables.')->group(function () {
        Route::get('/status', [TableSuggestionController::class, 'getTableStatus'])->name('status');
        Route::get('/suggestions', [TableSuggestionController::class, 'getSuggestions'])->name('suggestions');
        Route::post('/{tableId}/reserve', [TableSuggestionController::class, 'reserveTable'])->name('reserve');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/public', [SettingController::class, 'getPublicSettings'])->name('public');
        Route::get('/is-open', [SettingController::class, 'isStoreOpen'])->name('is-open');
        Route::get('/store-hours', [SettingController::class, 'getStoreHours'])->name('store-hours');
        Route::get('/today-hours', [SettingController::class, 'getTodayHours'])->name('today-hours');
        Route::get('/block-registration', [SettingController::class, 'shouldBlockRegistration'])->name('block-registration');
    });

    // Analytics
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/today', [AnalyticsController::class, 'getTodayAnalytics'])->name('today');
        Route::get('/date/{date}', [AnalyticsController::class, 'getAnalyticsByDate'])->name('by-date');
        Route::get('/export-history', [AnalyticsController::class, 'getExportHistory'])->name('export-history');
    });

    // Utility
    Route::get('/csrf-token', fn() => response()->json(['csrf_token' => csrf_token()]))->name('csrf-token');
    Route::get('/current-wait-time', [ApiController::class, 'getCurrentWaitTime'])->name('current-wait-time');

});
