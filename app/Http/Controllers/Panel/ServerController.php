<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;

class ServerController extends PanelController
{

    /**
     * Display servers owned by the authenticated user.
     *
     * Route: GET /panel/servers/all
     */
    public function all()
    {
        $userId = (string) auth()->id();

        $basePath = storage_path(
            'app/private/zeitops/servers'
        );

        $servers = collect();

        if (File::isDirectory($basePath)) {
            foreach (File::directories($basePath) as $directory) {

                $metadataPath = $directory
                    . DIRECTORY_SEPARATOR
                    . 'server.json';

                if (!File::isFile($metadataPath)) {
                    continue;
                }

                try {
                    $metadata = json_decode(
                        File::get($metadataPath),
                        true,
                        512,
                        JSON_THROW_ON_ERROR
                    );
                } catch (\Throwable $e) {
                    report($e);
                    continue;
                }

                if (!is_array($metadata)) {
                    continue;
                }

                // Never display another user's server.
                if (
                    !isset($metadata['user_id']) ||
                    (string) $metadata['user_id'] !== $userId
                ) {
                    continue;
                }

                $serverId = $metadata['server_id']
                    ?? basename($directory);

                $ip = $metadata['ip_address']
                    ?? $metadata['ip']
                    ?? $metadata['host']
                    ?? null;

                $port = $metadata['port']
                    ?? $metadata['ssh_port']
                    ?? 22;

                $rawStatus = strtolower(
                    (string) ($metadata['status'] ?? 'pending')
                );

                $status = match ($rawStatus) {
                    'connected', 'verified', 'active'
                    => 'connected',

                    'failed', 'error', 'disconnected'
                    => 'failed',

                    default => 'pending',
                };

                $fingerprint = $metadata['host_fingerprint']
                    ?? $metadata['ssh_host_fingerprint']
                    ?? $metadata['verified_host_fingerprint']
                    ?? null;

                $lastChecked = $metadata['last_checked_at']
                    ?? null;

                $lastConnected = $metadata['last_connected_at']
                    ?? null;

                $formatDate = static function ($value) {
                    if (!$value) {
                        return null;
                    }

                    try {
                        return Carbon::parse($value)
                            ->format('M j, Y H:i');
                    } catch (\Throwable $e) {
                        return null;
                    }
                };

                $servers->push([
                    'id' => $serverId,

                    'name' => $metadata['name']
                        ?? $metadata['server_name']
                            ?? 'Server ' . substr($serverId, 0, 8),

                    'ip' => $ip,
                    'port' => (int) $port,

                    'username' => $metadata['ssh_username']
                        ?? $metadata['username']
                            ?? 'zeitops',

                    'status' => $status,

                    'host_verified' => !empty($fingerprint),

                    'fingerprint' => $fingerprint,

                    'last_checked_at' => $formatDate($lastChecked),

                    'last_connected_at' => $formatDate($lastConnected),

                    'last_error' => $metadata['last_error']
                        ?? null,

                    'created_at' => $metadata['created_at']
                        ?? null,
                ]);
            }
        }

        // Most recently registered servers first.
        $servers = $servers->sortByDesc(
            fn ($server) => $server['created_at'] ?? ''
        )->values();

        $stats = [
            'total' => $servers->count(),

            'connected' => $servers->where(
                'status',
                'connected'
            )->count(),

            'pending' => $servers->where(
                'status',
                'pending'
            )->count(),

            'failed' => $servers->where(
                'status',
                'failed'
            )->count(),
        ];

        return view('panel.servers.all', [
            'servers' => $servers,
            'stats' => $stats,
        ]);
    }

    public function add()
    {
        return view('panel.servers.add');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required',
            'ip' => 'required|ip',
            'port' => 'required|numeric',
            'username' => 'required|string',
            'password' => 'required',
        ]);

        $ip = $validated['ip'];

        $publicKey = 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQChV81+BNIDttCoi/P5VXgKeUJZ5sPtKDmCkGN0H6NLUBPOKGwSsBKWKirHzodpGWD2s1K/aPnYg1gRDq/2FaJE9/odP5Gwp7QOmp3vKeqkfaJzRkZKCaXEr7M/GUqOXa5WIxHsN/iXqpfWLXKAUudTyRppFLdICb+rDn9MjH2XG45pfstdxdUtrVokHolBYoxHWGbJufmm+cf85D85C0WZ9AEXlMVdmsB6yRXfAb94jG6OA7cIuxiEnlazVlaQsRUP8oIhAWAxXVX8HrjRStB0i/rvNdAopYpAf24ZiFEN+XEWxhtzaA+3ZLKN1i7GRjYnrm2gVRLvL1+mx4ASjteTZ1vvCdTaduAvYV15LFwj8nwLfMszvqeSiucZhXVSwEluRUP84zVT5aVX+C16bnkdMpUsfkrV+35TTSc8Vm4aYp7nevzuApMbW/otLKTdKqct0m6598xay0AbyDY4UphxNGZPQpZTDr18hRC8rggbtYdzioH6DUUUGpn69AYQmuwdMR4sITJhEE8jyDsP6F/iYkVVrZs63DHvOLpGGiMH5kS8hhngtvRlV9QXytdljUQwJKpe57SAmWBRqFM208aTGFx+JWMdL38JHTiBpSt4G4pC6WvsgbUQPp0AaeC2H97iQAzF0IFyF6sZMW0rW9XCShWopKvR6EbBD0gc9BzaBQ== ezzeit@hackalone';

        $command = <<<EOT
# Create user 'ezzeit' if not exists
sudo id -u ezzeit >/dev/null 2>&1 || sudo useradd -m -s /bin/bash ezzeit
sudo usermod -aG sudo ezzeit

# Setup SSH for ezzeit
sudo mkdir -p /home/ezzeit/.ssh
sudo tee /home/ezzeit/.ssh/authorized_keys > /dev/null <<'EOF'
$publicKey
EOF
sudo chown -R ezzeit:ezzeit /home/ezzeit/.ssh
sudo chmod 700 /home/ezzeit/.ssh
sudo chmod 600 /home/ezzeit/.ssh/authorized_keys
EOT;

        return view('panel.servers.add', compact('validated', 'command', 'ip'));
    }

    public function check($ip)
    {
        // config / env overrides
        $sshUser = env('SSH_USER', 'ezzeit');
        $sshKeyPath = env('SSH_KEY_PATH', '/home/ezzeit/.ssh/id_rsa');
        $sshTimeout = 5; // seconds

        // Ping check (Linux/Mac style). Adjust if running on Windows.
        $escapedIp = escapeshellarg($ip);
        $pingCmd = "ping -c 2 -W {$sshTimeout} {$escapedIp} 2>&1";
        $pingOutput = [];
        $pingReturn = 1;
        @exec($pingCmd, $pingOutput, $pingReturn);
        $pingStatus = ($pingReturn === 0);

        // SSH check
        $sshStatus = [
            'ok' => false,
            'message' => 'Not checked',
        ];

        // Try phpseclib if available
        if (class_exists(\phpseclib3\Net\SSH2::class) && class_exists(\phpseclib3\Crypt\PublicKeyLoader::class)) {
            try {
                $keyContents = @file_get_contents($sshKeyPath);
                if ($keyContents === false) {
                    $sshStatus['ok'] = false;
                    $sshStatus['message'] = "Private key not readable at {$sshKeyPath}";
                } else {
                    $key = \phpseclib3\Crypt\PublicKeyLoader::load($keyContents);
                    $ssh = new \phpseclib3\Net\SSH2($ip, 22, $sshTimeout);
                    $login = $ssh->login($sshUser, $key);
                    if ($login) {
                        // run a harmless command
                        $result = $ssh->exec('echo __SSH_OK__');
                        if (strpos($result, '__SSH_OK__') !== false) {
                            $sshStatus['ok'] = true;
                            $sshStatus['message'] = 'Authenticated via phpseclib';
                        } else {
                            $sshStatus['ok'] = false;
                            $sshStatus['message'] = 'Connected but unexpected response';
                        }
                    } else {
                        $sshStatus['ok'] = false;
                        $sshStatus['message'] = 'Authentication failed (phpseclib)';
                    }
                }
            } catch (\Throwable $e) {
                $sshStatus['ok'] = false;
                $sshStatus['message'] = 'phpseclib error: ' . $e->getMessage();
            }
        } else {
            // Fallback to system ssh (non-interactive)
            $escapedKey = escapeshellarg($sshKeyPath);
            $escapedUserAtHost = escapeshellarg("{$sshUser}@{$ip}");
            $sshCmd = "ssh -o BatchMode=yes -o ConnectTimeout={$sshTimeout} -i {$escapedKey} {$escapedUserAtHost} echo __SSH_OK__ 2>&1";
            $sshOutput = [];
            $sshReturn = 1;
            @exec($sshCmd, $sshOutput, $sshReturn);
            $out = implode("\n", $sshOutput);
            if ($sshReturn === 0 && strpos($out, '__SSH_OK__') !== false) {
                $sshStatus['ok'] = true;
                $sshStatus['message'] = 'Authenticated via system ssh';
            } else {
                $sshStatus['ok'] = false;
                $sshStatus['message'] = trim($out) ?: 'SSH command failed';
            }
        }

        return view('panel.servers.check', [
            'ip' => $ip,
            'pingStatus' => $pingStatus,
            'pingOutput' => $pingOutput,
            'sshStatus' => $sshStatus,
        ]);
    }

    public function all()
    {
        return view('panel.servers.all');
    }
}
