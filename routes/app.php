<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Panel\DashboardController;
use App\Http\Controllers\Panel\ServerController;
use App\Http\Controllers\Panel\ActivityController;
use App\Http\Controllers\Panel\WebsiteController;
use App\Http\Controllers\Panel\WebsiteProvisioningController;
use App\Http\Controllers\Panel\ProfileSettingsController;
use App\Http\Controllers\Panel\ServerBootstrapController;


Route::prefix('panel')->name('panel.')->middleware(['auth'])->group(function () {

    Route::get('/profile-settings', [
        ProfileSettingsController::class,
        'index'
    ])->name('profile-settings');

    Route::put('/profile-settings', [
        ProfileSettingsController::class,
        'update'
    ])->name('profile-settings.update');

    Route::put('/profile-settings/password', [
        ProfileSettingsController::class,
        'updatePassword'
    ])->name('profile-settings.password');

    Route::get('/dashboard', [DashboardController::class, 'showDashboard'])->name('dashboard');

    Route::get('/notifications', [ActivityController::class, 'index'])->name('notifications');

    Route::post('/notifications/mark-read', [ActivityController::class, 'markRead'])
        ->name('notifications.markRead')
        ->middleware('auth');


    Route::prefix('servers')
        ->name('servers.')
        ->group(function () {

            Route::get('/add', [
                ServerController::class,
                'add'
            ])->name('add');

            Route::post('/store', [
                ServerController::class,
                'store'
            ])->name('store');

            Route::get('/all', [
                ServerController::class,
                'all'
            ])->name('all');

            Route::get('/{serverId}/manage', [
                ServerController::class,
                'manage'
            ])
                ->whereUuid('serverId')
                ->name('manage');

            // Existing legacy route
            Route::get('/check/{ip}', [
                ServerController::class,
                'check'
            ])->name('check');

            Route::post('/{serverId}/bootstrap', [
                ServerController::class,
                'bootstrap'
            ])
                ->whereUuid('serverId')
                ->middleware([
                    'password.confirm',
                    'throttle:3,10',
                ])
                ->name('bootstrap');

            Route::post('/{serverId}/bootstrap', [
                ServerBootstrapController::class,
                'store'
            ])
                ->whereUuid('serverId')
                ->middleware(['password.confirm', 'throttle:3,10'])
                ->name('bootstrap');
        });


    Route::prefix('websites')
        ->name('websites.')
        ->group(function () {

            /*
            |--------------------------------------------------------------------------
            | Website Listing
            |--------------------------------------------------------------------------
            */

            Route::get('/all', [
                WebsiteController::class,
                'all'
            ])->name('all');


            /*
            |--------------------------------------------------------------------------
            | Website Setup Page
            |--------------------------------------------------------------------------
            */

            Route::get('/{website}/setup', [
                WebsiteController::class,
                'setup'
            ])
                ->whereNumber('website')
                ->name('setup');


            /*
            |--------------------------------------------------------------------------
            | Provisioning Status API
            |--------------------------------------------------------------------------
            |
            | Available to authenticated website owners.
            | Does not require an active membership.
            |
            */

            Route::get('/{website}/provision/status', [
                WebsiteProvisioningController::class,
                'status'
            ])
                ->whereNumber('website')
                ->name('provision.status');


            /*
            |--------------------------------------------------------------------------
            | Active Membership Required
            |--------------------------------------------------------------------------
            */

            Route::middleware(['active.membership'])
                ->group(function () {

                    // Show Create Website Form
                    Route::get('/create', [
                        WebsiteController::class,
                        'create'
                    ])->name('create');


                    // Save Website Configuration
                    Route::post('/', [
                        WebsiteController::class,
                        'store'
                    ])->name('store');


                    // Start Server Provisioning
                    Route::post('/{website}/provision', [
                        WebsiteProvisioningController::class,
                        'start'
                    ])
                        ->whereNumber('website')
                        ->name('provision.start');

                });

        });

});
