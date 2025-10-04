<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('kiosk.attract-screen');
})->name('kiosk.attract');

// Terms & Conditions Routes
Route::post('/kiosk/terms/accept', [\App\Http\Controllers\TermsConsentController::class, 'accept'])->name('kiosk.terms.accept');
Route::post('/kiosk/terms/decline', [\App\Http\Controllers\TermsConsentController::class, 'decline'])->name('kiosk.terms.decline');

// Table Suggestion API Routes
Route::get('/api/kiosk/table-suggestions', [\App\Http\Controllers\TableSuggestionController::class, 'getSuggestions'])->name('api.table-suggestions');
Route::post('/api/kiosk/reserve-table/{tableId}', [\App\Http\Controllers\TableSuggestionController::class, 'reserveTable'])->name('api.reserve-table');
Route::get('/api/tables/status', [\App\Http\Controllers\TableSuggestionController::class, 'getTableStatus'])->name('api.tables-status');

// Settings API Routes
Route::get('/api/settings/public', [\App\Http\Controllers\SettingsController::class, 'getPublicSettings'])->name('api.settings.public');
Route::post('/api/admin/settings/update', [\App\Http\Controllers\SettingsController::class, 'updateSettings'])->name('api.admin.settings.update');

// Admin Settings Page
Route::get('/admin/settings', function () {
    return view('admin.settings');
})->name('admin.settings');

Route::get('/kiosk/guest-info', [\App\Http\Controllers\KioskController::class, 'guestInfo'])->name('kiosk.guest-info');
Route::post('/kiosk/guest-info', [\App\Http\Controllers\KioskController::class, 'storeGuestInfo'])->name('kiosk.store-guest-info');
Route::get('/kiosk/registration', [\App\Http\Controllers\RegistrationController::class, 'show'])->name('kiosk.registration');
Route::post('/kiosk/registration', [\App\Http\Controllers\RegistrationController::class, 'store'])->name('kiosk.registration.store');
Route::get('/kiosk/review-details', [\App\Http\Controllers\RegistrationController::class, 'reviewDetails'])->name('kiosk.review-details');
Route::post('/kiosk/id-verify', [\App\Http\Controllers\RegistrationController::class, 'verifyId'])->name('kiosk.id-verify');
Route::post('/kiosk/registration/confirm', [\App\Http\Controllers\RegistrationController::class, 'confirm'])->name('kiosk.registration.confirm');
Route::get('/kiosk/receipt/{customerId}', function ($customerId) {
    $customer = \App\Models\Customer::findOrFail($customerId);
    return view('kiosk.receipt', [
        'customer' => $customer
    ]);
})->name('kiosk.receipt');

Route::get('/kiosk/id-scanner', function () {
    return view('kiosk.staffverification');
})->name('kiosk.id-scanner');

Route::get('/kiosk/webcam-config', [App\Http\Controllers\Kiosk\IDScannerController::class, 'getWebcamConfig']);
Route::get('/kiosk/demo-qr', function () {
    return view('kiosk.demo-qr-generator');
})->name('kiosk.demo-qr');

Route::get('/kiosk/session-timeout', function () {
    return view('kiosk.session-timeout');
})->name('kiosk.session-timeout');

Route::get('/kiosk/test-timeout', function () {
    return view('kiosk.test-timeout');
})->name('kiosk.test-timeout');


Route::get('/kiosk/staff-assistance', function (Request $request) {
    return view('kiosk.staff-assistance', [
        'requestId' => $request->get('request_id'),
        'issueType' => $request->get('issue'),
        'priorityType' => $request->get('priority_type')
    ]);
})->name('kiosk.staff-assistance');

Route::get('/kiosk/queue-summary', function () {
    return view('kiosk.queue-summary');
})->name('kiosk.queue-summary');

// Dynamic queue management routes
Route::post('/api/queue/update-wait-times', [\App\Http\Controllers\RegistrationController::class, 'updateAllWaitTimes'])->name('api.queue.update-wait-times');
Route::get('/api/queue/stats', [\App\Http\Controllers\RegistrationController::class, 'getQueueStats'])->name('api.queue.stats');
Route::get('/api/customer/{queueNumber}/position', [\App\Http\Controllers\ApiController::class, 'getPosition'])->name('api.customer.position');
Route::get('/api/queue/summary', [\App\Http\Controllers\ApiController::class, 'getQueueSummary'])->name('api.queue.summary');

// Real-time queue endpoints using customer ID
Route::get('/api/customer/{customerId}/current-wait', [\App\Http\Controllers\ApiController::class, 'getCurrentWait'])->name('api.customer.current-wait');
Route::get('/api/current-wait-time', [\App\Http\Controllers\ApiController::class, 'getCurrentWaitTime'])->name('api.current-wait-time');

Route::get('/kiosk/debug-test', function () {
    return view('kiosk.debug-test');
})->name('kiosk.debug-test');

Route::post('/kiosk/check-duplicate-contact', [\App\Http\Controllers\RegistrationController::class, 'checkDuplicateContact'])->name('kiosk.check-duplicate-contact');
// ID Scanner and DroidCam routes
Route::get('/kiosk/droidcam/status', [\App\Http\Controllers\Kiosk\IDScannerController::class, 'checkStatus'])->name('kiosk.droidcam.status');
Route::post('/kiosk/droidcam/capture-image', [\App\Http\Controllers\Kiosk\IDScannerController::class, 'captureImage'])->name('kiosk.droidcam.capture-image')->withoutMiddleware('web');
Route::post('/kiosk/droidcam/capture-and-ocr', [\App\Http\Controllers\Kiosk\IDScannerController::class, 'captureAndOCR'])->name('kiosk.droidcam.capture-and-ocr')->withoutMiddleware('web');
Route::post('/kiosk/verify-id-name', [\App\Http\Controllers\Kiosk\IDScannerController::class, 'verifyIDName'])->name('kiosk.verify-id-name')->withoutMiddleware('web');
Route::post('/kiosk/request-staff-assistance', [\App\Http\Controllers\Kiosk\IDScannerController::class, 'requestStaffAssistance'])->name('kiosk.request-staff-assistance')->withoutMiddleware('web');
Route::get('/kiosk/id-mismatch', [\App\Http\Controllers\Kiosk\IDScannerController::class, 'showMismatchScreen'])->name('kiosk.id-mismatch');
Route::get('/kiosk/ocr-test', function () {
    return view('kiosk.ocr-test');
})->name('kiosk.ocr-test');
Route::get('/kiosk/camera-scanner', function () {
    return view('kiosk.camera-scanner');
})->name('kiosk.camera-scanner');


// Staff Dashboard
Route::get('/admin/priority-pin-dashboard', function () {
    return view('admin.priority-pin-dashboard');
})->name('admin.priority-pin-dashboard');

// Priority Verification API Routes
Route::post('/api/customer/request-verification', [\App\Http\Controllers\VerificationController::class, 'requestVerification'])->name('api.verification.request');
Route::get('/api/customer/verification-status/{id}', [\App\Http\Controllers\VerificationController::class, 'checkVerificationStatus'])->name('api.verification.status');
Route::get('/api/verification/pending', [\App\Http\Controllers\VerificationController::class, 'getPendingVerifications'])->name('api.verification.pending');
Route::post('/api/verification/complete', [\App\Http\Controllers\VerificationController::class, 'verifyAndGeneratePIN'])->name('api.verification.complete');

