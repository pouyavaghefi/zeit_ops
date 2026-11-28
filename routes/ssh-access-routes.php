<?php
// Merge inside existing Route::prefix('panel')->name('panel.')->middleware(['auth']) group.
// use App\Http\Controllers\Panel\ServerAccessKeyController; // at the top of routes/app.php
Route::get('/ssh-keys', [ServerAccessKeyController::class, 'select'])
    ->name('servers.ssh-keys.select');
Route::get('/servers/{serverId}/ssh-keys', [ServerAccessKeyController::class, 'index'])
    ->whereUuid('serverId')->name('servers.ssh-keys.index');
Route::middleware(['active.membership'])->group(function () {
    Route::post('/servers/{serverId}/ssh-keys', [ServerAccessKeyController::class, 'store'])
        ->whereUuid('serverId')->middleware('throttle:5,10')->name('servers.ssh-keys.store');
    Route::delete('/servers/{serverId}/ssh-keys/{key}', [ServerAccessKeyController::class, 'destroy'])
        ->whereUuid('serverId')->whereNumber('key')->middleware('throttle:5,10')->name('servers.ssh-keys.destroy');
});
