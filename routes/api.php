<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\Api\SettingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
// Customer verification routes
Route::prefix('customer')->group(function () {
    Route::post('/request-verification', [VerificationController::class, 'requestVerification']);
    Route::get('/verification-status/{id}', [VerificationController::class, 'checkVerificationStatus']);
});

// Staff verification routes
Route::prefix('staff')->group(function () {
    Route::get('/pending-verifications', [VerificationController::class, 'getPendingVerifications']);
    Route::post('/reject-verification', [VerificationController::class, 'rejectVerification']);
});

// Verification routes (for dashboard)
Route::prefix('verification')->group(function () {
    Route::post('/complete', [VerificationController::class, 'completeVerification']);
    Route::post('/reject', [VerificationController::class, 'rejectVerification']);
});

// Queue management routes
Route::prefix('queue')->group(function () {
    Route::get('/update', [\App\Http\Controllers\ApiController::class, 'updateQueueInfo']);
    Route::get('/status/{customerId}', [\App\Http\Controllers\ApiController::class, 'getCustomerQueueStatus']);
});

// Store hours and settings routes
Route::prefix('settings')->group(function () {
    Route::get('/is-open', [SettingController::class, 'isStoreOpen']);
    Route::get('/store-hours', [SettingController::class, 'getStoreHours']);
    Route::get('/today-hours', [SettingController::class, 'getTodayHours']);
    Route::get('/public', [SettingController::class, 'getPublicSettings']);
    Route::get('/block-registration', [SettingController::class, 'shouldBlockRegistration']);
});

// Analytics routes
Route::prefix('analytics')->group(function () {
    Route::get('/today', [\App\Http\Controllers\AnalyticsController::class, 'getTodayAnalytics']);
    Route::get('/date/{date}', [\App\Http\Controllers\AnalyticsController::class, 'getAnalyticsByDate']);
    Route::get('/export-history', [\App\Http\Controllers\AnalyticsController::class, 'getExportHistory']);
});

// Debug route
Route::get('/test-db', function() {
    try {
        \DB::table('priority_verifications')->count();
        return response()->json(['status' => 'success', 'message' => 'Database connected!']);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
});

