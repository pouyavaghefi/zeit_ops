
<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Panel\DashboardController;
use App\Http\Controllers\Panel\ServerAccessKeyController;
use App\Http\Controllers\Panel\ServerController;
use App\Http\Controllers\Panel\ActivityController;
use App\Http\Controllers\Panel\WebsiteController;
use App\Http\Controllers\Panel\WebsiteProvisioningController;
use App\Http\Controllers\Panel\WebsiteDeploymentController;
use App\Http\Controllers\Panel\WebsiteDomainController;
use App\Http\Controllers\Panel\WebsiteDnsController;
use App\Http\Controllers\Panel\WebsiteRedirectController;
use App\Http\Controllers\Panel\WebsiteUptimeController;
use App\Http\Controllers\Panel\ProfileSettingsController;
use App\Http\Controllers\Panel\ServerBootstrapController;

/*
|--------------------------------------------------------------------------
| Authenticated Panel Routes
|--------------------------------------------------------------------------
*/

Route::prefix('panel')
    ->name('panel.')
    ->middleware(['auth'])
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Profile Settings
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Dashboard & Notifications
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', [
            DashboardController::class,
            'showDashboard'
        ])->name('dashboard');

        Route::get('/notifications', [
            ActivityController::class,
            'index'
        ])->name('notifications');

        Route::post('/notifications/mark-read', [
            ActivityController::class,
            'markRead'
        ])->name('notifications.markRead');

        /*
        |--------------------------------------------------------------------------
        | Server Management
        |--------------------------------------------------------------------------
        */

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

                // Legacy server connection check.
                Route::get('/check/{ip}', [
                    ServerController::class,
                    'check'
                ])->name('check');

                Route::post('/{serverId}/bootstrap', [
                    ServerBootstrapController::class,
                    'store'
                ])
                    ->whereUuid('serverId')
                    ->middleware('throttle:3,10')
                    ->name('bootstrap');

                Route::post('/{serverId}/bootstrap-key', [
                    ServerBootstrapController::class,
                    'generateKey'
                ])
                    ->whereUuid('serverId')
                    ->middleware('throttle:3,10')
                    ->name('bootstrap-key.generate');
            });

        /*
        |--------------------------------------------------------------------------
        | Global Website Management Pages
        |--------------------------------------------------------------------------
        */

        // DNS Records website selection.
        Route::get('/dns-records', [
            WebsiteDnsController::class,
            'select'
        ])->name('dns.select');

        // Redirects website selection.
        Route::get('/redirects', [
            WebsiteRedirectController::class,
            'select'
        ])->name('redirects.select');

        // Uptime Monitoring website selection.
        Route::get('/uptime', [
            WebsiteUptimeController::class,
            'select'
        ])->name('uptime.select');

        /*
        |--------------------------------------------------------------------------
        | Website Management
        |--------------------------------------------------------------------------
        */

        Route::prefix('websites')
            ->name('websites.')
            ->group(function () {

                /*
                |--------------------------------------------------------------------------
                | Website Listing & Setup
                |--------------------------------------------------------------------------
                */

                Route::get('/all', [
                    WebsiteController::class,
                    'all'
                ])->name('all');

                Route::get('/{website}/setup', [
                    WebsiteController::class,
                    'setup'
                ])
                    ->whereNumber('website')
                    ->name('setup');

                /*
                |--------------------------------------------------------------------------
                | Server Provisioning Status
                |--------------------------------------------------------------------------
                */

                Route::get('/{website}/provision/status', [
                    WebsiteProvisioningController::class,
                    'status'
                ])
                    ->whereNumber('website')
                    ->name('provision.status');

                /*
                |--------------------------------------------------------------------------
                | SSL & Domains
                |--------------------------------------------------------------------------
                */

                Route::get('/{website}/domains', [
                    WebsiteDomainController::class,
                    'index'
                ])
                    ->whereNumber('website')
                    ->name('domains.index');

                Route::middleware('active.membership')
                    ->group(function () {

                        Route::post('/{website}/domains', [
                            WebsiteDomainController::class,
                            'store'
                        ])
                            ->whereNumber('website')
                            ->middleware('throttle:10,10')
                            ->name('domains.store');

                        Route::post('/{website}/domains/{domain}/verify', [
                            WebsiteDomainController::class,
                            'verify'
                        ])
                            ->whereNumber('website')
                            ->whereNumber('domain')
                            ->middleware('throttle:10,10')
                            ->name('domains.verify');

                        Route::post('/{website}/domains/{domain}/issue', [
                            WebsiteDomainController::class,
                            'issue'
                        ])
                            ->whereNumber('website')
                            ->whereNumber('domain')
                            ->middleware('throttle:4,10')
                            ->name('domains.issue');

                        Route::delete('/{website}/domains/{domain}', [
                            WebsiteDomainController::class,
                            'destroy'
                        ])
                            ->whereNumber('website')
                            ->whereNumber('domain')
                            ->middleware('throttle:4,10')
                            ->name('domains.destroy');
                    });


                /*
                |--------------------------------------------------------------------------
                | SSH Keys & Access
                |--------------------------------------------------------------------------
                */

// Select a registered server
                Route::get('/ssh-keys', [
                    ServerAccessKeyController::class,
                    'select'
                ])->name('servers.ssh-keys.select');

// Manage SSH keys for a server
                Route::get('/servers/{serverId}/ssh-keys', [
                    ServerAccessKeyController::class,
                    'index'
                ])
                    ->whereUuid('serverId')
                    ->name('servers.ssh-keys.index');

// Actions require an active membership
                Route::middleware(['active.membership'])->group(function () {

                    // Add a public SSH key
                    Route::post('/servers/{serverId}/ssh-keys', [
                        ServerAccessKeyController::class,
                        'store'
                    ])
                        ->whereUuid('serverId')
                        ->middleware('throttle:5,10')
                        ->name('servers.ssh-keys.store');

                    // Revoke an SSH key
                    Route::delete('/servers/{serverId}/ssh-keys/{key}', [
                        ServerAccessKeyController::class,
                        'destroy'
                    ])
                        ->whereUuid('serverId')
                        ->whereNumber('key')
                        ->middleware('throttle:5,10')
                        ->name('servers.ssh-keys.destroy');

                });

                /*
                |--------------------------------------------------------------------------
                | DNS Records
                |--------------------------------------------------------------------------
                */

                Route::get('/{website}/dns-records', [
                    WebsiteDnsController::class,
                    'index'
                ])
                    ->whereNumber('website')
                    ->name('dns.index');

                Route::middleware('active.membership')
                    ->group(function () {

                        Route::post('/{website}/dns-records/zones', [
                            WebsiteDnsController::class,
                            'connect'
                        ])
                            ->whereNumber('website')
                            ->middleware('throttle:5,10')
                            ->name('dns.connect');

                        Route::delete('/{website}/dns-records/zones/{zone}', [
                            WebsiteDnsController::class,
                            'disconnect'
                        ])
                            ->whereNumber('website')
                            ->whereNumber('zone')
                            ->middleware('throttle:5,10')
                            ->name('dns.disconnect');

                        Route::post('/{website}/dns-records/zones/{zone}/records', [
                            WebsiteDnsController::class,
                            'store'
                        ])
                            ->whereNumber('website')
                            ->whereNumber('zone')
                            ->middleware('throttle:10,10')
                            ->name('dns.records.store');

                        Route::patch('/{website}/dns-records/zones/{zone}/records/{record}', [
                            WebsiteDnsController::class,
                            'update'
                        ])
                            ->whereNumber('website')
                            ->whereNumber('zone')
                            ->middleware('throttle:10,10')
                            ->name('dns.records.update');

                        Route::delete('/{website}/dns-records/zones/{zone}/records/{record}', [
                            WebsiteDnsController::class,
                            'destroy'
                        ])
                            ->whereNumber('website')
                            ->whereNumber('zone')
                            ->middleware('throttle:10,10')
                            ->name('dns.records.destroy');
                    });

                /*
                |--------------------------------------------------------------------------
                | Redirects & Subdomains
                |--------------------------------------------------------------------------
                */

                Route::get('/{website}/redirects', [
                    WebsiteRedirectController::class,
                    'index'
                ])
                    ->whereNumber('website')
                    ->name('redirects.index');

                Route::middleware('active.membership')
                    ->group(function () {

                        Route::post('/{website}/redirects', [
                            WebsiteRedirectController::class,
                            'store'
                        ])
                            ->whereNumber('website')
                            ->middleware('throttle:6,10')
                            ->name('redirects.store');

                        Route::delete('/{website}/redirects/{redirect}', [
                            WebsiteRedirectController::class,
                            'destroy'
                        ])
                            ->whereNumber('website')
                            ->whereNumber('redirect')
                            ->middleware('throttle:6,10')
                            ->name('redirects.destroy');
                    });

                /*
                |--------------------------------------------------------------------------
                | Uptime Monitoring
                |--------------------------------------------------------------------------
                |
                | The global /panel/uptime route uses select().
                | The website-specific dashboard uses index($request, $website).
                |
                */

                Route::get('/{website}/uptime', [
                    WebsiteUptimeController::class,
                    'index'
                ])
                    ->whereNumber('website')
                    ->name('uptime.index');

                Route::middleware('active.membership')
                    ->group(function () {

                        // Enable monitoring.
                        Route::post('/{website}/uptime', [
                            WebsiteUptimeController::class,
                            'enable'
                        ])
                            ->whereNumber('website')
                            ->middleware('throttle:6,10')
                            ->name('uptime.enable');

                        // Update monitoring configuration.
                        Route::patch('/{website}/uptime', [
                            WebsiteUptimeController::class,
                            'update'
                        ])
                            ->whereNumber('website')
                            ->middleware('throttle:6,10')
                            ->name('uptime.update');

                        // Trigger an immediate uptime check.
                        Route::post('/{website}/uptime/check', [
                            WebsiteUptimeController::class,
                            'check'
                        ])
                            ->whereNumber('website')
                            ->middleware('throttle:6,10')
                            ->name('uptime.check');

                        // Disable or pause monitoring.
                        Route::delete('/{website}/uptime', [
                            WebsiteUptimeController::class,
                            'disable'
                        ])
                            ->whereNumber('website')
                            ->middleware('throttle:6,10')
                            ->name('uptime.disable');
                    });

                /*
                |--------------------------------------------------------------------------
                | Deployment Status
                |--------------------------------------------------------------------------
                */

                Route::get('/{website}/deploy/status', [
                    WebsiteDeploymentController::class,
                    'status'
                ])
                    ->whereNumber('website')
                    ->name('deploy.status');

                /*
                |--------------------------------------------------------------------------
                | Active Membership Required
                |--------------------------------------------------------------------------
                */

                Route::middleware('active.membership')
                    ->group(function () {

                        // Create website form.
                        Route::get('/create', [
                            WebsiteController::class,
                            'create'
                        ])->name('create');

                        // Register website.
                        Route::post('/', [
                            WebsiteController::class,
                            'store'
                        ])->name('store');

                        // Start a deployment.
                        Route::post('/{website}/deploy', [
                            WebsiteDeploymentController::class,
                            'start'
                        ])
                            ->whereNumber('website')
                            ->middleware('throttle:6,10')
                            ->name('deploy.start');

                        // Start server provisioning.
                        Route::post('/{website}/provision', [
                            WebsiteProvisioningController::class,
                            'start'
                        ])
                            ->whereNumber('website')
                            ->name('provision.start');
                    });
            });
    });
