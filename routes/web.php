<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
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
    $enrollmentToken = Str::random(64);

    // Create private directory
    $directory = storage_path(
        "app/private/zeitops/servers/{$serverId}"
    );

    if (!is_dir($directory) && !mkdir($directory, 0700, true)) {
        abort(500, 'Could not create SSH key directory.');
    }

    chmod($directory, 0700);

    // Generate unique Ed25519 key pair
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

    chmod($privateKeyPath, 0600);
    chmod($publicKeyPath, 0600);

    // Read and encode public key
    $publicKey = trim(file_get_contents($publicKeyPath));
    $publicKeyBase64 = base64_encode($publicKey);

    // Generate short, single-line enrollment command
    $template = <<<'BASH'
sudo bash -c 'set -e; id zeitops >/dev/null 2>&1 || useradd -m -s /bin/bash zeitops; h=$(getent passwd zeitops | cut -d: -f6); test -n "$h" && test -d "$h"; g=$(id -gn zeitops); install -d -m 700 -o zeitops -g "$g" "$h/.ssh"; f="$h/.ssh/authorized_keys"; touch "$f"; k=$(echo __PUBLIC_KEY_B64__ | base64 -d); grep -qxF "$k" "$f" || printf "%s\n" "$k" >> "$f"; chown "zeitops:$g" "$f"; chmod 600 "$f"; echo "ZeitOps installed successfully"'
BASH;

    $command = str_replace(
        '__PUBLIC_KEY_B64__',
        $publicKeyBase64,
        $template
    );

    // Save server metadata
    $metadata = [
        'id' => $serverId,
        'ip' => $ip,
        'port' => (int) $validated['port'],
        'username' => 'zeitops',
        'type' => $validated['type'],
        'private_key_path' => $privateKeyPath,
        'host_fingerprint' => null,
        'status' => 'pending',
        'created_at' => now()->toIso8601String(),
        'enrollment_token_hash' => hash('sha256', $enrollmentToken),
    ];

    $metadataPath = $directory . '/server.json';

    file_put_contents(
        $metadataPath,
        json_encode(
            $metadata,
            JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
        )
    );

    chmod($metadataPath, 0600);

    // Display generated command
    return view('test.new_server', compact(
        'validated',
        'command',
        'ip',
        'serverId',
        'enrollmentToken'
    ));

})->middleware('throttle:5,1')->name('servers.store');

Route::get('/servers/check/{serverId}', function (Request $request, string $serverId) {

    // 1. Validate server ID
    abort_unless(Str::isUuid($serverId), 404);

    // 2. Locate server files
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

    // 3. Verify enrollment token
    $providedToken = $request->header('X-Enrollment-Token', '');

    $expectedHash = $server['enrollment_token_hash'] ?? '';

    abort_unless(
        is_string($providedToken) &&
        $providedToken !== '' &&
        is_string($expectedHash) &&
        $expectedHash !== '' &&
        hash_equals(
            $expectedHash,
            hash('sha256', $providedToken)
        ),
        403,
        'Invalid or missing enrollment token.'
    );

    // 4. Server configuration
    $ip = $server['ip'];
    $port = (int) $server['port'];
    $sshUser = 'zeitops';

    $sshStatus = [
        'ok' => false,
        'message' => 'Connection not checked',
    ];

    try {

        // 5. Validate SSH destination
        if (!filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4 |
            FILTER_FLAG_NO_PRIV_RANGE |
            FILTER_FLAG_NO_RES_RANGE
        )) {
            throw new RuntimeException(
                'A valid public server IP is required.'
            );
        }

        if ($port < 1 || $port > 65535) {
            throw new RuntimeException(
                'Invalid SSH port.'
            );
        }

        // 6. Check private key
        if (!is_readable($privateKeyPath)) {
            throw new RuntimeException(
                'ZeitOps SSH private key is not readable.'
            );
        }

        // 7. Require verified server identity
        if (empty($server['host_fingerprint'])) {
            throw new RuntimeException(
                'Server host fingerprint has not been verified.'
            );
        }

        // 8. Load private key
        $key = PublicKeyLoader::loadPrivateKey(
            file_get_contents($privateKeyPath)
        );

        // 9. Create SSH connection
        $ssh = new SSH2($ip, $port, 5);

        $ssh->setPreferredAlgorithms([
            'hostkey' => ['ssh-ed25519'],
        ]);

        // 10. Retrieve server host key
        $hostKey = $ssh->getServerPublicHostKey();

        if (!$hostKey) {
            throw new RuntimeException(
                'Unable to retrieve the SSH host key.'
            );
        }

        // 11. Calculate host fingerprint
        $parts = preg_split('/\s+/', trim($hostKey));

        if (($parts[0] ?? '') !== 'ssh-ed25519') {
            throw new RuntimeException(
                'Unexpected SSH host key type.'
            );
        }

        $keyBlob = base64_decode($parts[1] ?? '', true);

        if ($keyBlob === false) {
            throw new RuntimeException(
                'Invalid SSH host key.'
            );
        }

        $fingerprint = 'SHA256:' . rtrim(
                base64_encode(
                    hash('sha256', $keyBlob, true)
                ),
                '='
            );

        // 12. Verify server identity
        if (!hash_equals(
            $server['host_fingerprint'],
            $fingerprint
        )) {
            throw new RuntimeException(
                'SSH host fingerprint mismatch.'
            );
        }

        // 13. Authenticate
        if (!$ssh->login($sshUser, $key)) {
            throw new RuntimeException(
                'SSH authentication failed.'
            );
        }

        // 14. Execute harmless test command
        $result = trim($ssh->exec('whoami'));

        if ($result !== 'zeitops') {
            throw new RuntimeException(
                'SSH connected, but returned an unexpected user.'
            );
        }

        // 15. Success
        $sshStatus = [
            'ok' => true,
            'message' => 'SSH authentication successful',
        ];

    } catch (\Throwable $e) {

        logger()->warning('ZeitOps SSH connection check failed', [
            'server_id' => $serverId,
            'error' => $e->getMessage(),
        ]);

        $sshStatus = [
            'ok' => false,
            'message' => 'SSH connection check failed: ' . $e->getMessage(),
        ];

    }

    // 16. Return JSON
    return response()->json([
        'success' => $sshStatus['ok'],
        'server' => [
            'id' => $serverId,
            'ip' => $ip,
            'port' => $port,
            'username' => $sshUser,
        ],
        'ssh' => $sshStatus,
        'message' => $sshStatus['message'],
    ]);

})->middleware('throttle:5,1')->name('servers.check');
