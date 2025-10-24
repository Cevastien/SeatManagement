<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\TermsConsentController;

/*
|--------------------------------------------------------------------------
| Kiosk Routes
|--------------------------------------------------------------------------
| Routes for customer-facing kiosk interface
*/

// Attract Screen
Route::get('/', fn() => view('kiosk.attract-screen'))->name('kiosk.attract');

// Terms & Conditions
Route::post('/terms/accept', [TermsConsentController::class, 'accept'])->name('kiosk.terms.accept');
Route::post('/terms/decline', [TermsConsentController::class, 'decline'])->name('kiosk.terms.decline');

// Registration Flow
Route::prefix('registration')->name('registration.')->group(function () {
    Route::get('/', [RegistrationController::class, 'show'])->name('show');
    Route::post('/', [RegistrationController::class, 'store'])->name('store');
    Route::post('/confirm', [RegistrationController::class, 'confirm'])->name('confirm');
    Route::post('/cancel', [RegistrationController::class, 'cancel'])->name('cancel');
    Route::post('/check-duplicate-contact', [RegistrationController::class, 'checkDuplicateContact'])->name('check-duplicate');
});

// Review & Verification
Route::prefix('review')->name('review.')->group(function () {
    Route::get('/details', [RegistrationController::class, 'reviewDetails'])->name('details');
    Route::post('/update', [RegistrationController::class, 'updateReviewDetails'])->name('update');
    Route::post('/id-verify', [RegistrationController::class, 'verifyId'])->name('id-verify');
    Route::get('/check-verification-status', [RegistrationController::class, 'checkVerificationStatus'])->name('check-status');
    Route::post('/update-verification-session', [RegistrationController::class, 'updateVerificationSession'])->name('update-session');
});

// Receipt
Route::get('/receipt/{customerId}', function ($customerId) {
    $customer = \App\Models\Customer::findOrFail($customerId);
    return view('kiosk.receipt', compact('customer'));
})->name('kiosk.receipt');

// Staff Verification
Route::get('/staffverification', fn() => view('kiosk.staffverification'))->name('kiosk.staffverification');

// Queue Display
Route::get('/queue-summary', fn() => view('kiosk.queue-summary'))->name('kiosk.queue-summary');

// Utility Views
Route::get('/session-timeout', fn() => view('kiosk.session-timeout'))->name('kiosk.session-timeout');
Route::get('/staff-assistance', fn(\Illuminate\Http\Request $request) => view('kiosk.staff-assistance', [
    'requestId' => $request->get('request_id'),
    'issueType' => $request->get('issue'),
    'priorityType' => $request->get('priority_type')
]))->name('kiosk.staff-assistance');
