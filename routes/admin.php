<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\SettingsController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
| Routes for staff/admin dashboard and management
*/

// TODO: Add authentication middleware
// Route::middleware(['auth', 'role:admin'])->group(function () {

// Priority Management Dashboard
Route::get('/priority-pin-dashboard', fn() => view('admin.priority-pin-dashboard'))
    ->name('admin.priority-pin-dashboard');

// Settings
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', fn() => view('admin.settings'))->name('index');
    Route::post('/update', [SettingsController::class, 'updateSettings'])->name('update');
});

// Analytics
Route::prefix('analytics')->name('analytics.')->group(function () {
    Route::get('/dashboard', [AnalyticsController::class, 'dashboard'])->name('dashboard');
    Route::get('/export/today', [AnalyticsController::class, 'exportToday'])->name('export.today');
});

// Customer Management
Route::get('/customers', fn() => view('admin.customers'))->name('admin.customers');

// });
