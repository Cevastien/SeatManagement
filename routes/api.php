<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VerificationController;

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
    Route::post('/verify-and-generate-pin', [VerificationController::class, 'verifyAndGeneratePIN']);
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

