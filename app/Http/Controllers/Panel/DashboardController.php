<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends PanelController
{
    public function showDashboard(Request  $request)
    {
        $userId = (int) $request->user()->id;

        $basePath = storage_path(
            'app/private/zeitops/servers'
        );

        $servers = collect();

        if (is_dir($basePath)) {
            foreach (glob($basePath . '/*/server.json') ?: [] as $path) {

                // Ignore symlinks and invalid files.
                if (is_link($path) ||
                    is_link(dirname($path)) ||
                    !is_file($path) ||
                    !is_readable($path)) {
                    continue;
                }

                $metadata = json_decode(
                    file_get_contents($path),
                    true
                );

                if (!is_array($metadata)) {
                    continue;
                }

                // Never display another user's servers.
                if ((int) ($metadata['user_id'] ?? 0) !== $userId) {
                    continue;
                }

                $id = $metadata['id'] ?? null;

                if (!is_string($id) ||
                    !\Illuminate\Support\Str::isUuid($id) ||
                    basename(dirname($path)) !== $id) {
                    continue;
                }

                $servers->push([
                    'id' => $id,
                    'ip' => $metadata['ip'] ?? 'Unknown',
                    'port' => $metadata['port'] ?? 22,
                    'type' => $metadata['type'] ?? null,
                    'status' => $metadata['status'] ?? 'pending',
                    'fingerprint_verified' =>
                        !empty($metadata['host_fingerprint']),
                    'last_checked_at' =>
                        $metadata['last_checked_at'] ?? null,
                    'last_connected_at' =>
                        $metadata['last_connected_at'] ?? null,
                    'created_at' =>
                        $metadata['created_at'] ?? null,
                ]);
            }
        }

        $servers = $servers
            ->sortByDesc('created_at')
            ->values();

        $stats = [
            'total' => $servers->count(),

            'connected' => $servers
                ->where('status', 'connected')
                ->count(),

            'pending' => $servers
                ->where('status', 'pending')
                ->count(),

            'failed' => $servers
                ->where('status', 'failed')
                ->count(),
        ];

        return view('panel.dashboard.index', compact(
            'servers',
            'stats'
        ));
    }
}
