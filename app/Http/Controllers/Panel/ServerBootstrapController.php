<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\Provisioning\ServerBootstrapService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ServerBootstrapController extends Controller
{
    public function store(
        Request $request,
        string $serverId,
        ServerBootstrapService $bootstrap
    ): RedirectResponse {
        abort_unless(Str::isUuid($serverId), 404);

        $path = storage_path(
            "app/private/zeitops/servers/{$serverId}/server.json"
        );
        abort_unless(File::isFile($path), 404);

        try {
            $server = json_decode(
                File::get($path), true, 512, JSON_THROW_ON_ERROR
            );
        } catch (Throwable $e) {
            abort(404);
        }

        abort_unless(
            is_array($server) &&
            ($server['id'] ?? null) === $serverId &&
            (string) ($server['user_id'] ?? '') === (string) $request->user()->id,
            404
        );

        $redirect = fn () => redirect()->route('panel.servers.manage', [
            'serverId' => $serverId,
        ]);

        // Manual validation: never flash a sensitive private key into session old input.
        $validator = Validator::make($request->all(), [
            'admin_username' => ['required', 'string', 'regex:/^[a-z_][a-z0-9_-]{0,31}$/'],
            'admin_private_key' => ['required', 'string', 'max:20000'],
            'admin_key_passphrase' => ['nullable', 'string', 'max:4096'],
            'confirm_bootstrap' => ['required', 'accepted'],
        ]);

        if ($validator->fails()) {
            return $redirect()->withErrors($validator)->withInput(
                $request->only('admin_username')
            );
        }

        if (($server['status'] ?? null) !== 'connected') {
            return $redirect()->withErrors([
                'bootstrap' => 'Verify the VPS SSH connection first.',
            ]);
        }

        if (($server['provisioning_status'] ?? null) === 'ready') {
            return $redirect()->with('success', 'Server is already provision-ready.');
        }

        $values = $validator->validated();

        try {
            $result = $bootstrap->bootstrapWithKey(
                serverId: $serverId,
                ownerId: (int) $request->user()->id,
                adminUsername: $values['admin_username'],
                privateKey: $values['admin_private_key'],
                passphrase: $values['admin_key_passphrase'] ?? null
            );

            return $redirect()->with('success', $result['message']);
        } catch (Throwable $e) {
            // Log only safe metadata. Never log the request or raw SSH exception.
            Log::channel('provisioning')->error('Bootstrap authorization failed', [
                'server_id' => $serverId,
                'owner_id' => $request->user()->id,
                'exception_class' => get_class($e),
            ]);

            return $redirect()->withErrors([
                'bootstrap' => 'Bootstrap failed. Review provisioning logs and SSH administrator access.',
            ]);
        }
    }
}
