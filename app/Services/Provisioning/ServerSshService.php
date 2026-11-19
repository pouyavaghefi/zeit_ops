<?php

namespace App\Services\Provisioning;

use Illuminate\Support\Facades\File;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SSH2;
use RuntimeException;

class ServerSshService
{
    public function connect(
        string $serverId,
        int $ownerId
    ): SSH2 {
        $base = storage_path(
            "app/private/zeitops/servers/{$serverId}"
        );

        $metadataFile = $base . '/server.json';
        $privateKeyFile = $base . '/id_ed25519';

        if (
            !File::isFile($metadataFile) ||
            !File::isFile($privateKeyFile)
        ) {
            throw new RuntimeException(
                'Server credentials are unavailable.'
            );
        }

        $metadata = json_decode(
            File::get($metadataFile),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if (
            !is_array($metadata) ||
            ($metadata['id'] ?? null) !== $serverId ||
            (string) ($metadata['user_id'] ?? '') !== (string) $ownerId ||
            ($metadata['status'] ?? null) !== 'connected'
        ) {
            throw new RuntimeException(
                'Server ownership or connection status is invalid.'
            );
        }

        $ip = $metadata['ip']
            ?? $metadata['ip_address']
            ?? null;

        if (!is_string($ip) ||
            !filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new RuntimeException(
                'The registered server IP is invalid.'
            );
        }

        $port = (int) ($metadata['port'] ?? 22);

        if ($port < 1 || $port > 65535) {
            throw new RuntimeException('Invalid SSH port.');
        }

        $username = $metadata['username']
            ?? $metadata['ssh_user']
            ?? 'zeitops';

        if (!is_string($username) ||
            !preg_match('/^[a-z_][a-z0-9_-]{0,31}$/', $username)) {
            throw new RuntimeException('Invalid SSH user.');
        }

        // Adapt this field to the trusted fingerprint field
        // written by your existing enrollment code.
        $expectedFingerprint =
            $metadata['host_fingerprint_sha256']
            ?? $metadata['host_fingerprint']
            ?? $metadata['fingerprint']
            ?? null;

        if (!is_string($expectedFingerprint) ||
            !str_starts_with($expectedFingerprint, 'SHA256:')) {
            throw new RuntimeException(
                'No trusted SSH host fingerprint is recorded.'
            );
        }

        $ssh = new SSH2($ip, $port, 15);
        $ssh->setTimeout(120);

        // Retrieve and validate the host key BEFORE login.
        $hostKey = $ssh->getServerPublicHostKey();

        if (!is_string($hostKey)) {
            throw new RuntimeException(
                'Unable to retrieve the SSH host key.'
            );
        }

        $parts = preg_split('/\s+/', trim($hostKey));

        if (count($parts) < 2) {
            throw new RuntimeException('Malformed SSH host key.');
        }

        $keyBytes = base64_decode($parts[1], true);

        if ($keyBytes === false) {
            throw new RuntimeException('Invalid SSH host key encoding.');
        }

        $actualFingerprint = 'SHA256:' . rtrim(
                base64_encode(hash('sha256', $keyBytes, true)),
                '='
            );

        if (!hash_equals(
            $expectedFingerprint,
            $actualFingerprint
        )) {
            throw new RuntimeException(
                'SSH host fingerprint mismatch. Connection refused.'
            );
        }

        $privateKey = PublicKeyLoader::loadPrivateKey(
            File::get($privateKeyFile)
        );

        if (!$ssh->login($username, $privateKey)) {
            throw new RuntimeException(
                'SSH private key authentication failed.'
            );
        }

        return $ssh;
    }

    public function execute(
        SSH2 $ssh,
        string $command,
        int $timeout = 120
    ): string {
        $ssh->setTimeout($timeout);

        $output = $ssh->exec($command);
        $exitCode = $ssh->getExitStatus();

        if ($output === false || $exitCode !== 0) {
            throw new RuntimeException(
                'Remote command failed or timed out.'
            );
        }

        return trim($output);
    }
}
