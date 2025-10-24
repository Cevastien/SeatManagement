<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\{
    RegistrationController,
    TermsConsentController,
    AnalyticsController,
    VerificationController,
    ApiController,
    TableSuggestionController,
    SettingsController
};

/*
|--------------------------------------------------------------------------
| Web Routes - Cleaned & Organized
|--------------------------------------------------------------------------
*/

// Root - Kiosk Attract Screen
Route::get('/', fn() => view('kiosk.attract-screen'))->name('kiosk.attract');

/*
|--------------------------------------------------------------------------
| Kiosk Routes (Customer-Facing)
|--------------------------------------------------------------------------
*/
Route::prefix('kiosk')->name('kiosk.')->group(function () {
    // Terms & Conditions
    Route::post('/terms/accept', [TermsConsentController::class, 'accept'])->name('terms.accept');
    Route::post('/terms/decline', [TermsConsentController::class, 'decline'])->name('terms.decline');

    // Registration Flow
    Route::get('/registration', [RegistrationController::class, 'show'])->name('registration');
    Route::post('/registration', [RegistrationController::class, 'store'])->name('registration.store');
    Route::post('/registration/confirm', [RegistrationController::class, 'confirm'])->name('registration.confirm');
    Route::post('/registration/cancel', [RegistrationController::class, 'cancel'])->name('registration.cancel');
    Route::post('/check-duplicate-contact', [RegistrationController::class, 'checkDuplicateContact'])->name('check-duplicate-contact');

    // Review & Verification
    Route::get('/review-details', [RegistrationController::class, 'reviewDetails'])->name('review-details');
    Route::post('/review-details/update', [RegistrationController::class, 'updateReviewDetails'])->name('review-details.update');
    Route::post('/id-verify', [RegistrationController::class, 'verifyId'])->name('id-verify');
    Route::get('/check-verification-status', [RegistrationController::class, 'checkVerificationStatus'])->name('check-verification-status');
    Route::post('/update-verification-session', [RegistrationController::class, 'updateVerificationSession'])->name('update-verification-session');

    // Receipt
    Route::get('/receipt/{customerId}', fn($customerId) => view('kiosk.receipt', [
        'customer' => \App\Models\Customer::findOrFail($customerId)
    ]))->name('receipt');

    // Staff & Displays
    Route::get('/staffverification', fn() => view('kiosk.staffverification'))->name('staffverification');
    Route::get('/queue-summary', fn() => view('kiosk.queue-summary'))->name('queue-summary');
    Route::get('/staff-assistance', fn(Request $req) => view('kiosk.staff-assistance', [
        'requestId' => $req->get('request_id'),
        'issueType' => $req->get('issue'),
        'priorityType' => $req->get('priority_type')
    ]))->name('staff-assistance');

    // Utility Views
    Route::get('/session-timeout', fn() => view('kiosk.session-timeout'))->name('session-timeout');
});

/*
|--------------------------------------------------------------------------
| Admin & Staff Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/settings', fn() => view('admin.settings'))->name('settings');
    Route::get('/priority-pin-dashboard', fn() => view('admin.priority-pin-dashboard'))->name('priority-pin-dashboard');
});

Route::prefix('staff')->name('staff.')->group(function () {
    Route::get('/analytics/dashboard', [AnalyticsController::class, 'dashboard'])->name('analytics.dashboard');
    Route::get('/analytics/export/today', [AnalyticsController::class, 'exportToday'])->name('analytics.export.today');
});

/*
|--------------------------------------------------------------------------
| API Routes (Internal - Move to api.php later)
|--------------------------------------------------------------------------
*/
Route::prefix('api')->name('api.')->middleware('rate.limit.api:120,1')->group(function () {
    // Queue Management
    Route::prefix('queue')->name('queue.')->group(function () {
        Route::get('/stats', [ApiController::class, 'getQueueStats'])->name('stats');
        Route::get('/summary', [ApiController::class, 'getQueueSummary'])->name('summary');
        Route::get('/update', [ApiController::class, 'getQueueUpdate'])->name('update');
        Route::post('/update-wait-times', [RegistrationController::class, 'updateAllWaitTimes'])->name('update-wait-times');
    });

    // Customer Info
    Route::prefix('customer')->name('customer.')->group(function () {
        Route::get('/{customerId}/current-wait', [ApiController::class, 'getCurrentWait'])->name('current-wait');
        Route::get('/{queueNumber}/position', [ApiController::class, 'getPosition'])->name('position');
        Route::post('/request-verification', [VerificationController::class, 'requestVerification'])->name('request-verification');
        Route::get('/verification-status/{id}', [VerificationController::class, 'checkVerificationStatus'])->name('verification-status');
    });

    // Verification
    Route::prefix('verification')->name('verification.')->group(function () {
        Route::get('/pending', [VerificationController::class, 'getPendingVerifications'])->name('pending');
        Route::get('/completed', [VerificationController::class, 'getCompletedVerifications'])->name('completed');
        Route::post('/complete', [VerificationController::class, 'completeVerification'])->name('complete');
        Route::post('/reject', [VerificationController::class, 'rejectVerification'])->name('reject');
    });

    // Tables
    Route::prefix('tables')->name('tables.')->group(function () {
        Route::get('/status', [TableSuggestionController::class, 'getTableStatus'])->name('status');
        Route::get('/suggestions', [TableSuggestionController::class, 'getSuggestions'])->name('suggestions');
        Route::post('/{tableId}/reserve', [TableSuggestionController::class, 'reserveTable'])->name('reserve');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/public', [SettingsController::class, 'getPublicSettings'])->name('public');
        Route::post('/update', [SettingsController::class, 'updateSettings'])->name('update');
    });

    // Utility
    Route::get('/csrf-token', fn() => response()->json(['csrf_token' => csrf_token()]))->name('csrf-token');
    Route::get('/current-wait-time', [ApiController::class, 'getCurrentWaitTime'])->name('current-wait-time');
});

