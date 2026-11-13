<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;

require __DIR__.'/auth.php';
require __DIR__.'/app.php';

Route::get('/', function () {
    return view('frontend.index');
});

Route::get('/nl', function () {
    return view('frontend.dutch');
});

Route::get('/de', function () {
    return view('frontend.de');
});

// Show new server form
Route::get('/servers/new', function () {
    return view('test.new_server');
})->name('servers.new');

// Handle new server form submit
Route::post('/servers/new', function (Request $request) {

    $validated = $request->validate([
        'type' => 'required|string',
        'ip' => 'required|ipv4',
        'port' => 'required|integer|between:1,65535',
        'username' => 'required|string',
    ]);

    $ip = $validated['ip'];
    $serverId = (string) Str::uuid();

    // 1. Create a private directory for this server
    $directory = storage_path(
        "app/private/zeitops/servers/{$serverId}"
    );

    if (!is_dir($directory)) {
        if (!mkdir($directory, 0700, true)) {
            abort(500, 'Could not create SSH key directory.');
        }
    }

    chmod($directory, 0700);

    // 2. Generate a unique Ed25519 SSH key pair
    $privateKeyPath = $directory . '/id_ed25519';
    $publicKeyPath = $privateKeyPath . '.pub';

    $process = new Process([
        'ssh-keygen',
        '-q',
        '-t', 'ed25519',
        '-N', '',
        '-C', "zeitops-server-{$serverId}",
        '-f', $privateKeyPath,
    ]);

    $process->mustRun();

    // 3. Secure private key permissions
    chmod($privateKeyPath, 0600);
    chmod($publicKeyPath, 0600);

    // 4. Read the generated public key
    $publicKey = trim(
        file_get_contents($publicKeyPath)
    );

    // 5. Encode public key for the shell command
    $publicKeyBase64 = base64_encode($publicKey);

    // 6. Generate one-line enrollment command
    $template = <<<'BASH'
sudo bash -c 'set -e; id -u zeitops >/dev/null 2>&1 || useradd -m -s /bin/bash zeitops; h=$(getent passwd zeitops | cut -d: -f6); test -n "$h" && test -d "$h" || exit 1; g=$(id -gn zeitops); install -d -m 700 -o zeitops -g "$g" "$h/.ssh"; f="$h/.ssh/authorized_keys"; touch "$f"; chown "zeitops:$g" "$f"; chmod 600 "$f"; k=$(printf %s __PUBLIC_KEY_B64__ | base64 -d); grep -qxF "$k" "$f" || printf "%s\n" "$k" >> "$f"; echo "ZeitOps enrollment completed successfully"'
BASH;

    $command = str_replace(
        '__PUBLIC_KEY_B64__',
        $publicKeyBase64,
        $template
    );

    // 7. Save server metadata for Check Access
    $metadata = [
        'id' => $serverId,
        'ip' => $validated['ip'],
        'port' => (int) $validated['port'],
        'username' => 'zeitops',
        'type' => $validated['type'],
        'private_key_path' => $privateKeyPath,
        'status' => 'pending',
        'created_at' => now()->toIso8601String(),
    ];

    file_put_contents(
        $directory . '/server.json',
        json_encode($metadata, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)
    );

    chmod($directory . '/server.json', 0600);

    // 8. Display the generated command
    return view('test.new_server', compact(
        'validated',
        'command',
        'ip',
        'serverId'
    ));

})->name('servers.store');

Route::get('/servers/check/{serverId}', function (string $serverId) {

    abort_unless(Str::isUuid($serverId), 404);

    $directory = storage_path(
        "app/private/zeitops/servers/{$serverId}"
    );

    $metadataPath = $directory . '/server.json';
    $privateKeyPath = $directory . '/id_ed25519';

    abort_unless(is_file($metadataPath), 404);

    $server = json_decode(
        file_get_contents($metadataPath),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    // Only the server's owner may check its connection.
    abort_unless(
        isset($server['user_id']) &&
        (int) $server['user_id'] === (int) auth()->id(),
        403
    );

    $ip = $server['ip'];
    $port = (int) $server['port'];
    $sshUser = 'zeitops';

    $pingStatus = false;
    $pingOutput = [];

    $sshStatus = [
        'ok' => false,
        'message' => 'Not checked',
    ];

    try {

        if (!is_readable($privateKeyPath)) {
            throw new RuntimeException(
                'The SSH private key is not accessible.'
            );
        }

        if (empty($server['host_fingerprint'])) {
            throw new RuntimeException(
                'Server identity must be verified first.'
            );
        }

        $key = PublicKeyLoader::loadPrivateKey(
            file_get_contents($privateKeyPath)
        );

        $ssh = new SSH2($ip, $port, 5);

        // Require an Ed25519 server host key.
        $ssh->setPreferredAlgorithms([
            'hostkey' => ['ssh-ed25519'],
        ]);

        $hostKey = $ssh->getServerPublicHostKey();

        if (!$hostKey) {
            throw new RuntimeException(
                'Could not retrieve the SSH server host key.'
            );
        }

        $parts = preg_split('/\s+/', trim($hostKey));
        $keyBlob = base64_decode($parts[1] ?? '', true);

        if ($keyBlob === false) {
            throw new RuntimeException(
                'Invalid SSH server host key.'
            );
        }

        $fingerprint = 'SHA256:' . rtrim(
                base64_encode(hash('sha256', $keyBlob, true)),
                '='
            );

        if (!hash_equals(
            $server['host_fingerprint'],
            $fingerprint
        )) {
            throw new RuntimeException(
                'SSH server identity verification failed.'
            );
        }

        if (!$ssh->login($sshUser, $key)) {
            throw new RuntimeException(
                'SSH authentication failed.'
            );
        }

        $result = trim($ssh->exec('whoami'));

        if ($result !== 'zeitops') {
            throw new RuntimeException(
                'Connected, but the SSH username was unexpected.'
            );
        }

        $sshStatus = [
            'ok' => true,
            'message' => 'SSH authentication successful',
        ];

    } catch (\Throwable $e) {

        $sshStatus = [
            'ok' => false,
            'message' => $e->getMessage(),
        ];

    }

    return view('test.check', [
        'ip' => $ip,
        'port' => $port,
        'pingStatus' => $pingStatus,
        'pingOutput' => $pingOutput,
        'sshStatus' => $sshStatus,
        'serverId' => $serverId,
    ]);

})->name('servers.check');
