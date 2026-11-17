<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Panel\DashboardController;
use App\Http\Controllers\Panel\ServerController;
use App\Http\Controllers\Panel\ActivityController;
use App\Http\Controllers\Panel\WebsiteController;
use App\Http\Controllers\Panel\WebsiteProvisioningController;


Route::prefix('panel')->name('panel.')->middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'showDashboard'])->name('dashboard');

    Route::get('/notifications', [ActivityController::class, 'index'])->name('notifications');

    Route::post('/notifications/mark-read', [ActivityController::class, 'markRead'])
        ->name('notifications.markRead')
        ->middleware('auth');

    Route::prefix('servers')->name('servers.')->group(function () {

        Route::get('/add', [ServerController::class, 'add'])->name('add');

        Route::post('/store', [ServerController::class, 'store'])->name('store');

        Route::get('/all', [ServerController::class, 'all'])->name('all');

        Route::get('/check/{ip}', [ServerController::class, 'check'])->name('check');

    });

    Route::prefix('websites')
        ->name('websites.')
        ->group(function () {

            // =====================================
            // VIEW ALL WEBSITES
            // =====================================

            Route::get('/all', [
                WebsiteController::class,
                'all'
            ])->name('all');

            // =====================================
            // WEBSITE SETUP PAGE
            // =====================================

            Route::get('/{website}/setup', [
                WebsiteController::class,
                'setup'
            ])
                ->whereNumber('website')
                ->name('setup');

            // =====================================
            // VIEW PROVISIONING PROGRESS
            // =====================================

            Route::get('/{website}/provision/status', [
                WebsiteProvisioningController::class,
                'status'
            ])
                ->whereNumber('website')
                ->name('provision.status');

            // =====================================
            // ACTIVE MEMBERSHIP REQUIRED
            // =====================================

            Route::middleware('active.membership')
                ->group(function () {

                    // Add New Website
                    Route::get('/create', [
                        WebsiteController::class,
                        'create'
                    ])->name('create');

                    // Save Website
                    Route::post('/', [
                        WebsiteController::class,
                        'store'
                    ])->name('store');

                    // Start VPS Provisioning
                    Route::post('/{website}/provision', [
                        WebsiteProvisioningController::class,
                        'start'
                    ])
                        ->whereNumber('website')
                        ->name('provision.start');

                });

        });

});
