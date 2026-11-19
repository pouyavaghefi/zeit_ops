<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\Provisioning\ServerBootstrapService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Throwable;

class ServerBootstrapController extends Controller
{
    private const TEMP_KEY_TTL = 1800; // 30 minutes

    private function ownedServer(string $serverId, Request $request): array
    {
        abort_unless(Str::isUuid($serverId), 404);
        $dir = storage_path("app/private/zeitops/servers/{$serverId}");
        $path = $dir . '/server.json';
        abort_unless(File::isFile($path), 404);
        try {
            $server = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            abort(404);
        }
        abort_unless(
            is_array($server) &&
            ($server['id'] ?? null) === $serverId &&
            (string) ($server['user_id'] ?? '') === (string) $request->user()->id,
            404
        );
        return [$dir, $server];
    }

    public function generateKey(Request $request, string $serverId): RedirectResponse
    {
        [$dir, $server] = $this->ownedServer($serverId, $request);
        $redirect = fn () => redirect()->route('panel.servers.manage', ['serverId' => $serverId]);
        if (($server['status'] ?? null) !== 'connected' ||
            ($server['provisioning_status'] ?? null) === 'ready') {
            return $redirect()->withErrors(['bootstrap' => 'Server must be connected and not yet provision-ready.']);
        }
        $keyPath = $dir . '/bootstrap_admin_ed25519';
        $pubPath = $keyPath . '.pub';
        $lockHandle = fopen($dir . '/bootstrap.lock', 'c');
        if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
            if (is_resource($lockHandle)) fclose($lockHandle);
            return $redirect()->withErrors(['bootstrap' => 'Another bootstrap operation is in progress.']);
        }
        try {
            // Keep a still-valid key; never rotate it while the user is copying it.
            if (is_file($keyPath) && is_file($pubPath) &&
                time() - filemtime($keyPath) < self::TEMP_KEY_TTL) {
                return $redirect()->with('success', 'Use the generated public key shown below.');
            }
            @unlink($keyPath);
            @unlink($pubPath);
            $process = new Process([
                'ssh-keygen', '-q', '-t', 'ed25519', '-N', '',
                '-C', "zeitops-bootstrap-{$serverId}", '-f', $keyPath,
            ]);
            $process->setTimeout(20);
            $process->mustRun();
            chmod($keyPath, 0600);
            chmod($pubPath, 0600);
            return $redirect()->with('success', 'Temporary administrator public key generated. Authorize it once in the VPS console.');
        } catch (Throwable $e) {
            Log::channel('provisioning')->error('Temporary administrator key generation failed', [
                'server_id' => $serverId, 'exception_class' => get_class($e),
            ]);
            return $redirect()->withErrors(['bootstrap' => 'Unable to generate the temporary administrator key.']);
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }

    public function store(
        Request $request,
        string $serverId,
        ServerBootstrapService $bootstrap
    ): RedirectResponse {
        [$dir, $server] = $this->ownedServer($serverId, $request);
        $redirect = fn () => redirect()->route('panel.servers.manage', ['serverId' => $serverId]);
        $mode = $request->input('authorization_method', 'existing_key');
        if (!in_array($mode, ['existing_key', 'generated_key'], true)) {
            return $redirect()->withErrors(['bootstrap' => 'Invalid authorization method.']);
        }
        $rules = [
            'authorization_method' => ['required', 'in:existing_key,generated_key'],
            'confirm_bootstrap' => ['required', 'accepted'],
        ];
        if ($mode === 'existing_key') {
            $rules += [
                'admin_username' => ['required', 'string', 'regex:/^[a-z_][a-z0-9_-]{0,31}$/'],
                'admin_private_key' => ['required', 'string', 'max:20000'],
                'admin_key_passphrase' => ['nullable', 'string', 'max:4096'],
            ];
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            // Do not put credentials in Laravel's flashed old input.
            return $redirect()->withErrors($validator)->withInput($request->only('admin_username'));
        }
        if (($server['status'] ?? null) !== 'connected') {
            return $redirect()->withErrors(['bootstrap' => 'Verify SSH connection before bootstrapping.']);
        }
        if (($server['provisioning_status'] ?? null) === 'ready') {
            return $redirect()->with('success', 'Server is already provision-ready.');
        }
        try {
            if ($mode === 'generated_key') {
                $keyPath = $dir . '/bootstrap_admin_ed25519';
                $pubPath = $keyPath . '.pub';
                if (!File::isFile($keyPath) || !File::isFile($pubPath) ||
                    time() - filemtime($keyPath) >= self::TEMP_KEY_TTL ||
                    is_link($keyPath) || is_link($pubPath)) {
                    return $redirect()->withErrors(['bootstrap' => 'Temporary key is missing or expired. Generate another key.']);
                }
                $result = $bootstrap->bootstrapWithKey(
                    serverId: $serverId,
                    ownerId: (int) $request->user()->id,
                    adminUsername: 'root',
                    privateKey: File::get($keyPath),
                    passphrase: null,
                    cleanupPublicKey: trim(File::get($pubPath))
                );
                // Remote public key has already been revoked by the service.
                @unlink($keyPath);
                @unlink($pubPath);
            } else {
                $values = $validator->validated();
                $result = $bootstrap->bootstrapWithKey(
                    serverId: $serverId,
                    ownerId: (int) $request->user()->id,
                    adminUsername: $values['admin_username'],
                    privateKey: $values['admin_private_key'],
                    passphrase: $values['admin_key_passphrase'] ?? null
                );
            }
            return $redirect()->with('success', $result['message']);
        } catch (Throwable $e) {
            Log::channel('provisioning')->error(
                'Bootstrap authorization failed',
                [
                    'server_id' => $serverId,
                    'owner_id' => $request->user()->id,
                    'authorization_method' => $mode,
                    'exception_class' => get_class($e),
                    'error_message' => $e instanceof \Error
                        ? $e->getMessage()
                        : 'See bootstrap phase logs',
                    'error_file' => basename($e->getFile()),
                    'error_line' => $e->getLine(),
                ]
            );

            return $redirect()->withErrors([
                'bootstrap' => 'Bootstrap failed. Check provisioning logs.'
            ]);
        }
    }
}
