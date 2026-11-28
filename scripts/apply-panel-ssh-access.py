#!/usr/bin/env python3
"""Guarded merge of ZeitOps SSH Keys routes and sidebar item.
Run from the root of your checked-out ZeitOps repository, after copying patch files.
"""
from pathlib import Path
import re
import sys

root = Path.cwd()
routes = root / 'routes/app.php'
sidebar = root / 'resources/views/panel/layouts/sidebar.blade.php'
if not routes.is_file() or not sidebar.is_file():
    sys.exit('Run from your ZeitOps Laravel project root (routes/app.php and sidebar must exist).')
r = routes.read_text()
s = sidebar.read_text()
controller_use = 'use App\\Http\\Controllers\\Panel\\ServerAccessKeyController;'
if controller_use not in r:
    if not r.startswith('<?php'):
        sys.exit('Unexpected routes/app.php format')
    r = r.replace('<?php', '<?php\n' + controller_use, 1)
route_marker = "    Route::prefix('websites')"
block = '''    // Infrastructure: SSH public keys and limited Linux access.
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

'''
if "->name('servers.ssh-keys.select')" not in r:
    if r.count(route_marker) != 1:
        sys.exit('Cannot locate a unique websites route group; manual merge required.')
    r = r.replace(route_marker, block + route_marker, 1)
pattern = r'<a\s+href="#">\s*<i\s+class="fas fa-key"></i>\s*SSH Keys &amp; Access\s*</a>|<a\s+href="#">\s*<i\s+class="fas fa-key"></i>\s*SSH Keys & Access\s*</a>'
replacement = '''<a href="{{ route('panel.servers.ssh-keys.select') }}"
                           class="{{ request()->routeIs('panel.servers.ssh-keys.*') ? 'active' : '' }}">
                            <i class="fas fa-key"></i>
                            SSH Keys &amp; Access
                        </a>'''
if "route('panel.servers.ssh-keys.select')" not in s:
    updated, count = re.subn(pattern, replacement, s, count=1)
    if count != 1:
        sys.exit('Cannot locate original SSH Keys & Access sidebar placeholder; no files written.')
    s = updated
# Do not partially mutate if either file failed validation.
for p in (routes, sidebar):
    backup = p.with_suffix(p.suffix + '.ssh-access.bak')
    if not backup.exists():
        backup.write_bytes(p.read_bytes())
routes.write_text(r)
sidebar.write_text(s)
print('Routes and sidebar integrated. Review git diff before committing.')
