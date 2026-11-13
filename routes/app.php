<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Panel\DashboardController;
use App\Http\Controllers\Panel\ServerController;
use App\Http\Controllers\Panel\ActivityController;

Route::prefix('panel')->name('panel.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'showDashboard'])->name('dashboard');
    Route::get('/notifications', [ActivityController::class, 'index'])->name('notifications');
    Route::post('/notifications/mark-read', [ActivityController::class, 'markRead'])
        ->name('notifications.markRead')
        ->middleware('auth');

    Route::prefix('servers')->name('servers.')->group(function () {
        Route::get('/add', [ServerController::class, 'add'])->name('add');
        Route::post('/store', [ServerController::class, 'store'])->name('store');
        Route::get('/check/{ip}', [ServerController::class, 'check'])->name('check');
    });
});
