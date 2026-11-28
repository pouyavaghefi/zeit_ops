<?php

namespace App\Services\Provisioning;

use RuntimeException;

class ServerAccessKeyService
{
    public function __construct(private readonly ServerSshService $sshService) {}

    /** Strictly accept bare OpenSSH Ed25519 public keys; no options or private keys. */
    public function normalizeKey(string $input): array
    {
        $parts = preg_split('/\s+/', trim($input));
        if (!$parts || count($parts) < 2 || count($parts) > 3 || $parts[0] !== 'ssh-ed25519') {
            throw new RuntimeException('Enter one bare ssh-ed25519 public key (not a private key).');
        }
        $encoded = $parts[1];
        if (strlen($encoded) > 120 || !preg_match('/^[A-Za-z0-9+\/]+={0,2}$/D', $encoded)) {
            throw new RuntimeException('Invalid SSH public-key encoding.');
        }
        $blob = base64_decode($encoded, true);
        if ($blob === false || strlen($blob) !== 51) {
            throw new RuntimeException('Invalid SSH Ed25519 key length.');
        }
        // SSH wire format: uint32(11), "ssh-ed25519", uint32(32), 32 bytes.
        if (unpack('N', substr($blob, 0, 4))[1] !== 11 ||
            substr($blob, 4, 11) !== 'ssh-ed25519' ||
            unpack('N', substr($blob, 15, 4))[1] !== 32) {
            throw new RuntimeException('Invalid SSH Ed25519 public-key structure.');
        }
        $canonical = base64_encode($blob);
        $fingerprint = 'SHA256:' . rtrim(base64_encode(hash('sha256', $blob, true)), '=');
        return ['public_key' => 'ssh-ed25519 ' . $canonical, 'blob' => $canonical, 'fingerprint' => $fingerprint];
    }

    public function add(string $serverId, int $userId, string $fingerprint, string $blob): void
    {
        // Only fixed-format strings validated locally. No untrusted shell metacharacters.
        if (!preg_match('/^SHA256:[A-Za-z0-9+\/]{43}$/D', $fingerprint) ||
            !preg_match('/^[A-Za-z0-9+\/]{68}$/D', $blob)) {
            throw new RuntimeException('Invalid key arguments.');
        }
        $ssh = $this->sshService->connect($serverId, $userId);
        $this->sshService->execute($ssh,
            'sudo -n /usr/local/libexec/zeitops/access-keys add ' . $fingerprint . ' ' . $blob, 30);
    }

    public function revoke(string $serverId, int $userId, string $fingerprint): void
    {
        if (!preg_match('/^SHA256:[A-Za-z0-9+\/]{43}$/D', $fingerprint)) {
            throw new RuntimeException('Invalid fingerprint.');
        }
        $ssh = $this->sshService->connect($serverId, $userId);
        $this->sshService->execute($ssh,
            'sudo -n /usr/local/libexec/zeitops/access-keys remove ' . $fingerprint, 30);
    }
}
