<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\ServerAccessKey;
use App\Services\Provisioning\ServerAccessKeyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class ServerAccessKeyController extends Controller
{
    private function ownedServer(Request $request, string $serverId): array
    {
        abort_unless(Str::isUuid($serverId), 404);
        $path = storage_path("app/private/zeitops/servers/{$serverId}/server.json");
        abort_unless(File::isFile($path) && !is_link($path), 404);
        try {
            $server = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            abort(404);
        }
        abort_unless(is_array($server) &&
            (string) ($server['user_id'] ?? '') === (string) $request->user()->id &&
            ($server['id'] ?? $server['server_id'] ?? null) === $serverId, 404);
        return $server;
    }

    public function select(Request $request): View
    {
        $dir = storage_path('app/private/zeitops/servers');
        $servers = collect();
        foreach (File::isDirectory($dir) ? File::directories($dir) : [] as $folder) {
            $id = basename($folder);
            if (!Str::isUuid($id)) continue;
            $path = $folder . '/server.json';
            if (!File::isFile($path) || is_link($path)) continue;
            try {
                $data = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
            } catch (Throwable) { continue; }
            if (!is_array($data) || (string) ($data['user_id'] ?? '') !== (string) $request->user()->id) continue;
            $servers->push([
                'id' => $id,
                'name' => $data['name'] ?? $data['server_name'] ?? ('Server ' . substr($id, 0, 8)),
                'ip' => $data['ip_address'] ?? $data['ip'] ?? 'Unknown',
                'status' => $data['status'] ?? 'unknown',
            ]);
        }
        return view('panel.servers.ssh-keys.select', compact('servers'));
    }

    public function index(Request $request, string $serverId): View
    {
        $server = $this->ownedServer($request, $serverId);
        $keys = ServerAccessKey::query()
            ->where('user_id', $request->user()->id)
            ->where('server_id', $serverId)
            ->orderByDesc('id')->get();
        return view('panel.servers.ssh-keys.index', compact('server', 'serverId', 'keys'));
    }

    public function store(Request $request, string $serverId, ServerAccessKeyService $service): RedirectResponse
    {
        $server = $this->ownedServer($request, $serverId);
        abort_unless(($server['status'] ?? null) === 'connected', 422);
        $validated = $request->validate([
            'label' => ['required','string','min:2','max:100'],
            'public_key' => ['required','string','max:500'],
            'confirm_access' => ['required','accepted'],
        ]);
        $back = fn () => redirect()->route('panel.servers.ssh-keys.index', ['serverId' => $serverId]);
        try {
            $normalized = $service->normalizeKey($validated['public_key']);
        } catch (RuntimeException $e) {
            return $back()->withErrors(['public_key' => $e->getMessage()]);
        }
        if (ServerAccessKey::where('user_id', $request->user()->id)
            ->where('server_id', $serverId)
            ->where('fingerprint', $normalized['fingerprint'])
            ->whereIn('status', ['pending','active','error'])->exists()) {
            return $back()->withErrors(['public_key' => 'This key is already registered for this server.']);
        }
        $key = ServerAccessKey::create([
            'user_id' => $request->user()->id,
            'server_id' => $serverId,
            'label' => $validated['label'],
            'fingerprint' => $normalized['fingerprint'],
            'public_key' => $normalized['public_key'],
            'status' => 'pending',
        ]);
        try {
            $service->add($serverId, (int) $request->user()->id, $normalized['fingerprint'], $normalized['blob']);
            $key->update(['status'=>'active', 'installed_at'=>now(), 'last_error'=>null]);
            Log::info('SSH access public key added', ['server_id'=>$serverId, 'user_id'=>$request->user()->id, 'key_id'=>$key->id]);
            return $back()->with('success', 'SSH access granted to the limited zeitops-access account.');
        } catch (Throwable $e) {
            $key->update(['status'=>'error','last_error'=>'Remote key installation could not be verified.']);
            Log::warning('SSH access key installation failed', ['server_id'=>$serverId,'key_id'=>$key->id,'error_class'=>get_class($e)]);
            return $back()->withErrors(['ssh'=>'Failed to install the key on the server. Check that the SSH access helper is installed.']);
        }
    }

    public function destroy(Request $request, string $serverId, int $key, ServerAccessKeyService $service): RedirectResponse
    {
        $this->ownedServer($request, $serverId);
        $record = ServerAccessKey::query()
            ->where('user_id', $request->user()->id)
            ->where('server_id', $serverId)
            ->findOrFail($key);
        $back = fn () => redirect()->route('panel.servers.ssh-keys.index', ['serverId' => $serverId]);
        if ($record->status === 'revoked') return $back();
        try {
            $service->revoke($serverId, (int) $request->user()->id, $record->fingerprint);
            $record->update(['status'=>'revoked','revoked_at'=>now(),'last_error'=>null]);
            Log::info('SSH access public key revoked', ['server_id'=>$serverId,'key_id'=>$record->id,'user_id'=>$request->user()->id]);
            return $back()->with('success', 'SSH access key revoked.');
        } catch (Throwable $e) {
            Log::warning('SSH access key revocation failed', ['server_id'=>$serverId,'key_id'=>$record->id,'error_class'=>get_class($e)]);
            return $back()->withErrors(['ssh'=>'Remote revocation could not be verified. Key status remains unchanged.']);
        }
    }
}
