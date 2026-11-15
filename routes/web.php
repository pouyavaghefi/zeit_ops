<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

// Shared enrollment lifetime (2 hours). Keep the login redirect check in sync.
const ZEITOPS_ENROLLMENT_TTL = 7200;
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
        'user_id' => null,
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

    session([
        'zeitops.pending_enrollment' => [
            'server_id' => $serverId,
            'token' => $enrollmentToken,
            'created_at' => now()->timestamp,
        ],
    ]);

    // Display generated command
    return view('test.new_server', compact(
        'validated',
        'command',
        'ip',
        'serverId',
        'enrollmentToken'
    ));

})->middleware('throttle:5,1')->name('servers.store');

Route::get('/servers/enroll/{serverId}', function (string $serverId) {

    abort_unless(Str::isUuid($serverId), 404);

    $pending = session('zeitops.pending_enrollment');

    // Verify this enrollment belongs to the current session
    abort_unless(
        is_array($pending) &&
        ($pending['server_id'] ?? null) === $serverId &&
        !empty($pending['token']),
        403
    );

    // The enrollment session is valid for two hours.
    $age = now()->timestamp - (int) ($pending['created_at'] ?? 0);
    abort_if($age < 0 || $age > ZEITOPS_ENROLLMENT_TTL, 403,
        'Your enrollment session has expired.');

    $directory = storage_path(
        "app/private/zeitops/servers/{$serverId}"
    );

    $metadataPath = $directory . '/server.json';
    $publicKeyPath = $directory . '/id_ed25519.pub';

    abort_unless(
        is_file($metadataPath) && is_file($publicKeyPath),
        404
    );

    $server = json_decode(
        file_get_contents($metadataPath),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    // Match the token to the stored enrollment record
    abort_unless(
        hash_equals(
            $server['enrollment_token_hash'] ?? '',
            hash('sha256', $pending['token'])
        ),
        403
    );

    // Claim this pending enrollment on the first authenticated visit.
    // A session token is required above; another user cannot take an owned record.
    $handle = fopen($metadataPath, 'c+');
    abort_unless($handle && flock($handle, LOCK_EX), 500);
    try {
        rewind($handle);
        $server = json_decode(stream_get_contents($handle), true, 512, JSON_THROW_ON_ERROR);
        abort_unless(hash_equals(
            $server['enrollment_token_hash'] ?? '',
            hash('sha256', $pending['token'])
        ), 403);
        abort_if(!empty($server['user_id']) &&
            (int) $server['user_id'] !== (int) auth()->id(), 403);

        if (empty($server['user_id'])) {
            $server['user_id'] = auth()->id();
            rewind($handle);
            if (!ftruncate($handle, 0) || fwrite($handle, json_encode(
                    $server, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
                )) === false) {
                abort(500, 'Could not claim enrollment.');
            }
            fflush($handle);
        }
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }

    // Store the original server ID for the view
    $ip = $server['ip'];
    $serverId = $server['id'];
    $enrollmentToken = $pending['token'];

    return view('test.enroll', compact(
        'ip',
        'server',
        'serverId',
        'enrollmentToken'
    ));

})->middleware('auth')->name('servers.enroll');

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

    // Require the authenticated owner AND the active enrollment session.
    $pending = $request->session()->get('zeitops.pending_enrollment');
    $age = now()->timestamp - (int) ($pending['created_at'] ?? 0);
    abort_unless(
        is_array($pending) &&
        ($pending['server_id'] ?? null) === $serverId &&
        is_string($pending['token'] ?? null) &&
        $age >= 0 && $age <= ZEITOPS_ENROLLMENT_TTL &&
        (int) ($server['user_id'] ?? 0) === (int) auth()->id() &&
        hash_equals(hash('sha256', $pending['token']),
            $server['enrollment_token_hash'] ?? ''),
        403, 'Enrollment access denied.'
    );

    \Log::info('ZeitOps fingerprint debugging', [
        'server_id' => $serverId,
        'metadata_path' => $metadataPath,
        'host_fingerprint' => $server['host_fingerprint'] ?? null,
    ]);

    // 3. Verify enrollment token
    $providedToken = $request->header('X-Enrollment-Token', '');

    $expectedHash = $server['enrollment_token_hash'] ?? '';

    abort_unless(
        is_string($providedToken) &&
        $providedToken !== '' &&
        hash_equals($pending['token'], $providedToken) &&
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

})->middleware(['auth', 'throttle:5,1'])->name('servers.check');


Route::post('/servers/enroll/{serverId}/verify-host', function (
    Request $request,
    string $serverId
) {
    abort_unless(Str::isUuid($serverId), 404);

    $validated = $request->validate([
        'fingerprint' => [
            'required',
            'string',
            'regex:/^SHA256:[A-Za-z0-9+\/]{43}$/',
        ],
    ]);

    $pending = $request->session()->get(
        'zeitops.pending_enrollment'
    );

    abort_unless(
        is_array($pending) &&
        ($pending['server_id'] ?? null) === $serverId &&
        is_string($pending['token'] ?? null) &&
        (now()->timestamp - (int) ($pending['created_at'] ?? 0))
        >= 0 &&
        (now()->timestamp - (int) ($pending['created_at'] ?? 0))
        <= ZEITOPS_ENROLLMENT_TTL,
        403,
        'Enrollment session is missing or expired.'
    );

    $path = storage_path(
        "app/private/zeitops/servers/{$serverId}/server.json"
    );

    abort_unless(is_file($path), 404);

    $server = json_decode(
        file_get_contents($path),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    abort_unless(
        !empty($server['enrollment_token_hash']) &&
        hash_equals(
            $server['enrollment_token_hash'],
            hash('sha256', $pending['token'])
        ),
        403
    );

    abort_unless((int) ($server['user_id'] ?? 0) === (int) auth()->id(),
        403, 'This enrollment is not assigned to your account.');

    // Do not silently replace a previously trusted SSH identity.
    abort_if(!empty($server['host_fingerprint']) &&
        !hash_equals($server['host_fingerprint'], $validated['fingerprint']),
        409, 'The server identity has already been verified.');

    // The user must independently compare this with the VPS console.
    $server['host_fingerprint'] = $validated['fingerprint'];

    $json = json_encode(
        $server,
        JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
    );

    // Write within the private enrollment directory.
    $tempPath = tempnam(dirname($path), 'metadata-');

    abort_if($tempPath === false, 500);

    try {
        if (file_put_contents($tempPath, $json, LOCK_EX) === false) {
            throw new RuntimeException('Unable to save fingerprint.');
        }

        chmod($tempPath, 0600);

        if (!rename($tempPath, $path)) {
            throw new RuntimeException('Unable to update enrollment.');
        }
    } finally {
        if (is_file($tempPath)) {
            unlink($tempPath);
        }
    }

    return redirect()
        ->route('servers.enroll', ['serverId' => $serverId])
        ->with('success', 'Server fingerprint confirmed.');

})->middleware(['auth', 'throttle:5,1'])
    ->name('servers.verify-host');
