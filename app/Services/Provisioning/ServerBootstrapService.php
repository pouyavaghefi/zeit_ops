<?php

namespace App\Services\Provisioning;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use phpseclib3\Net\SSH2;
use RuntimeException;
use Throwable;

/**
 * Installs the fixed ZeitOps provisioning helper on a registered Ubuntu VPS.
 * The caller must have authenticated the owner, obtained explicit permission
 * for administrator-level changes, and supplied one-time SSH credentials.
 * Do not place the password in sessions, metadata, a queue payload, or logs.
 */
final class ServerBootstrapService
{
    private const VERSION = '1.0.1';
    private const HELPER = '/usr/local/libexec/zeitops/provision';
    private const MANAGEMENT_USER = 'zeitops';

    public function bootstrap(
        string $serverId,
        int $ownerId,
        string $adminUsername,
        string $adminPassword
    ): array {
        if (!Str::isUuid($serverId)) {
            throw new RuntimeException('Invalid server ID.');
        }

        if (!preg_match('/^[a-z_][a-z0-9_-]{0,31}$/', $adminUsername)
            || $adminPassword === '') {
            throw new RuntimeException('Invalid administrator credentials.');
        }

        $directory = storage_path("app/private/zeitops/servers/{$serverId}");
        $metadataPath = $directory . '/server.json';
        $publicKeyPath = $directory . '/id_ed25519.pub';

        if (!File::isFile($metadataPath) || !File::isFile($publicKeyPath)) {
            throw new RuntimeException('Registered server files are missing.');
        }

        // This lock covers the entire remote bootstrap, not merely a JSON write.
        // All bootstrap entry points must call this service, not bypass it.
        $lockHandle = fopen($directory . '/bootstrap.lock', 'c');
        if ($lockHandle === false || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
            if (is_resource($lockHandle)) {
                fclose($lockHandle);
            }
            throw new RuntimeException('This server is already being bootstrapped.');
        }

        try {
            $server = $this->readMetadata($metadataPath);
            if (($server['id'] ?? null) !== $serverId
                || (string) ($server['user_id'] ?? '') !== (string) $ownerId
                || ($server['status'] ?? null) !== 'connected') {
                throw new RuntimeException('Server ownership or connection status is invalid.');
            }

            $ip = $server['ip'] ?? null;
            $port = (int) ($server['port'] ?? 22);
            if (!is_string($ip)
                || !filter_var($ip, FILTER_VALIDATE_IP,
                    FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)
                || $port < 1 || $port > 65535) {
                throw new RuntimeException('A valid public IPv4 address and SSH port are required.');
            }

            $expectedFingerprint = $server['host_fingerprint'] ?? null;
            if (!is_string($expectedFingerprint)
                || !preg_match('~^SHA256:[A-Za-z0-9+/]{43}$~', $expectedFingerprint)) {
                throw new RuntimeException('A previously verified SSH host fingerprint is required.');
            }

            $publicKey = trim(File::get($publicKeyPath));
            if (!preg_match('/^ssh-ed25519 [A-Za-z0-9+\/=]+(?: [^\r\n]*)?$/', $publicKey)) {
                throw new RuntimeException('Invalid registered SSH public key.');
            }

            // Never reset a ready server to bootstrapping without an explicit request.
            // The UI should request confirmation before calling this method.
            $this->updateMetadata($metadataPath, [
                'provisioning_status' => 'bootstrapping',
                'bootstrap_error' => null,
            ]);

            Log::channel('provisioning')->info('Server bootstrap started', [
                'server_id' => $serverId,
                'owner_id' => $ownerId,
            ]);

            $phase = 'ssh_identity';
            try {
                $ssh = new SSH2($ip, $port, 15);
                $ssh->setPreferredAlgorithms(['hostkey' => ['ssh-ed25519']]);
                $ssh->setTimeout(60);

                $hostKey = $ssh->getServerPublicHostKey();
                if (!is_string($hostKey)) {
                    throw new RuntimeException('Unable to retrieve the SSH host key.');
                }
                $parts = preg_split('/\s+/', trim($hostKey));
                if (($parts[0] ?? null) !== 'ssh-ed25519') {
                    throw new RuntimeException('Unexpected SSH host key algorithm.');
                }
                $rawHostKey = base64_decode($parts[1] ?? '', true);
                if ($rawHostKey === false) {
                    throw new RuntimeException('Malformed SSH host key.');
                }
                $actualFingerprint = 'SHA256:' . rtrim(
                        base64_encode(hash('sha256', $rawHostKey, true)), '='
                    );
                if (!hash_equals($expectedFingerprint, $actualFingerprint)) {
                    throw new RuntimeException('SSH host identity mismatch.');
                }

                $phase = 'administrator_authentication';
                if (!$ssh->login($adminUsername, $adminPassword)) {
                    throw new RuntimeException('Administrator SSH authentication failed.');
                }

                $phase = 'system_preflight';
                $os = $this->execute($ssh,
                    '. /etc/os-release && printf "%s:%s" "$ID" "$VERSION_ID"', 30);
                if ($os !== 'ubuntu:24.04') {
                    throw new RuntimeException('Only Ubuntu 24.04 is supported by this bootstrap helper.');
                }

                $uid = $this->execute($ssh, '/usr/bin/id -u', 30);
                if ($uid === '0') {
                    $rootShell = '/bin/bash -se';
                } else {
                    // Test exactly the privileged shell used for installation.
                    if ($this->execute($ssh, 'sudo -n /bin/bash -c "id -u"', 30) !== '0') {
                        throw new RuntimeException('Administrator has no noninteractive root shell.');
                    }
                    $rootShell = 'sudo -n /bin/bash -se';
                }

                $phase = 'install_helper';
                // Only server-generated fixed script content travels over SSH.
                // A failing bash command propagates through the remote exit status.
                $encodedScript = base64_encode($this->buildBootstrapScript($publicKey));
                $remoteCommand = 'printf %s ' . escapeshellarg($encodedScript)
                    . ' | /usr/bin/base64 -d | ' . $rootShell;
                $output = $this->execute($ssh, $remoteCommand, 300);
                if (!str_contains($output, 'BOOTSTRAP_SUCCESS')) {
                    throw new RuntimeException('Bootstrap script did not confirm success.');
                }

                $phase = 'verify_management_access';
                $management = app(ServerSshService::class)->connect($serverId, $ownerId);
                $check = $this->execute($management,
                    'sudo -n ' . self::HELPER . ' check', 30);
                if ($check !== '0') {
                    throw new RuntimeException('Management helper verification failed.');
                }

                $this->updateMetadata($metadataPath, [
                    'provisioning_status' => 'ready',
                    'bootstrap_version' => self::VERSION,
                    'bootstrap_completed_at' => now()->toIso8601String(),
                    'bootstrap_error' => null,
                ]);

                Log::channel('provisioning')->info('Server bootstrap completed', [
                    'server_id' => $serverId,
                    'version' => self::VERSION,
                ]);

                return [
                    'success' => true,
                    'server_id' => $serverId,
                    'provisioning_status' => 'ready',
                    'bootstrap_version' => self::VERSION,
                    'message' => 'Server management access configured and verified.',
                ];
            } catch (Throwable $e) {
                $this->updateMetadata($metadataPath, [
                    'provisioning_status' => 'failed',
                    'bootstrap_error' => 'Bootstrap failed during ' . $phase . '.',
                ]);
                // Avoid recording raw exceptions: third-party SSH errors may contain secrets.
                Log::channel('provisioning')->error('Server bootstrap failed', [
                    'server_id' => $serverId,
                    'phase' => $phase,
                    'exception_class' => get_class($e),
                ]);
                throw $e;
            }
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }

    private function readMetadata(string $path): array
    {
        $metadata = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($metadata)) {
            throw new RuntimeException('Server metadata must be a JSON object.');
        }
        return $metadata;
    }

    private function buildBootstrapScript(string $publicKey): string
    {
        return str_replace(
            ['__PUBLIC_KEY__', '__HELPER__', '__SUDOERS__'],
            [base64_encode($publicKey), base64_encode($this->helperScript()),
                base64_encode($this->sudoersPolicy())],
            <<<'BASH'
#!/usr/bin/env bash
set -euo pipefail
umask 077

[ "$(id -u)" -eq 0 ] || exit 1
command -v sudo >/dev/null
command -v visudo >/dev/null
command -v base64 >/dev/null

# Create the dedicated account and preserve any existing authorized keys.
if ! id zeitops >/dev/null 2>&1; then
    /usr/sbin/useradd -m -s /bin/bash zeitops
fi
HOME_DIR="$(getent passwd zeitops | cut -d: -f6)"
GROUP="$(id -gn zeitops)"
[ -n "$HOME_DIR" ] && [ -d "$HOME_DIR" ]
/usr/bin/install -d -m 700 -o zeitops -g "$GROUP" "$HOME_DIR/.ssh"
AUTHORIZED="$HOME_DIR/.ssh/authorized_keys"
[ ! -L "$AUTHORIZED" ] || { echo 'Refusing linked authorized_keys' >&2; exit 1; }
/usr/bin/touch "$AUTHORIZED"
/usr/bin/chown zeitops:"$GROUP" "$AUTHORIZED"
/usr/bin/chmod 600 "$AUTHORIZED"
PUBLIC_KEY="$(printf %s '__PUBLIC_KEY__' | /usr/bin/base64 -d)"
/usr/bin/grep -qxF -- "$PUBLIC_KEY" "$AUTHORIZED" ||
    printf '%s\n' "$PUBLIC_KEY" >> "$AUTHORIZED"

# Fixed root-owned helper. Atomic replacement avoids symlink writes.
/usr/bin/install -d -m 755 -o root -g root /usr/local/libexec/zeitops
HELPER_TMP="$(/usr/bin/mktemp /usr/local/libexec/zeitops/.provision-XXXXXX)"
printf %s '__HELPER__' | /usr/bin/base64 -d > "$HELPER_TMP"
/usr/bin/chown root:root "$HELPER_TMP"
/usr/bin/chmod 755 "$HELPER_TMP"
/usr/bin/mv -fT "$HELPER_TMP" /usr/local/libexec/zeitops/provision

# Validate sudoers content before activating it. Keep the file root-owned.
POLICY_TMP="$(/usr/bin/mktemp /etc/sudoers.d/.zeitops-XXXXXX)"
printf %s '__SUDOERS__' | /usr/bin/base64 -d > "$POLICY_TMP"
/usr/bin/chown root:root "$POLICY_TMP"
/usr/bin/chmod 440 "$POLICY_TMP"
/usr/sbin/visudo -cf "$POLICY_TMP" >/dev/null
/usr/bin/mv -fT "$POLICY_TMP" /etc/sudoers.d/zeitops-provision
/usr/bin/chmod 440 /etc/sudoers.d/zeitops-provision

# Confirm privilege policy without installing any packages.
/usr/sbin/runuser -u zeitops -- /usr/bin/sudo -n \
    /usr/local/libexec/zeitops/provision check | /usr/bin/grep -qx 0
printf 'BOOTSTRAP_SUCCESS\n'
BASH
        );
    }

    private function helperScript(): string
    {
        return <<<'BASH'
#!/usr/bin/env bash
set -euo pipefail
[ "$#" -eq 1 ] || { echo 'Exactly one operation is required' >&2; exit 2; }
export DEBIAN_FRONTEND=noninteractive
case "$1" in
    check)
        /usr/bin/id -u
        ;;
    update)
        /usr/bin/apt-get update -qq
        ;;
    install-apache)
        /usr/bin/apt-get install -y --no-install-recommends apache2
        ;;
    install-php83)
        /usr/bin/apt-get install -y --no-install-recommends \
            php8.3-cli php8.3-fpm php8.3-common php8.3-mysql \
            php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip \
            php8.3-bcmath php8.3-intl
        ;;
    install-tools)
        /usr/bin/apt-get install -y --no-install-recommends \
            mysql-server composer git unzip curl ca-certificates
        ;;
    *)
        echo 'Unknown provisioning operation' >&2
        exit 2
        ;;
esac
BASH;
    }

    private function sudoersPolicy(): string
    {
        $commands = array_map(
            static fn (string $operation) => self::HELPER . ' ' . $operation,
            ['check', 'update', 'install-apache', 'install-php83', 'install-tools']
        );

        // A single line avoids continuation errors; sudoers matches exact arguments.
        return 'zeitops ALL=(root) NOPASSWD: ' . implode(', ', $commands) . "\n";
    }

    private function execute(SSH2 $ssh, string $command, int $timeout = 120): string
    {
        $ssh->setTimeout($timeout);
        $output = $ssh->exec($command);
        $exit = $ssh->getExitStatus();
        if ($output === false || $ssh->isTimeout() || $exit !== 0) {
            Log::channel('provisioning')->warning('Bootstrap SSH operation failed', [
                'exit_code' => $exit,
                'timed_out' => $ssh->isTimeout(),
            ]);
            throw new RuntimeException('Remote bootstrap operation failed (exit ' .
                ($exit === null ? 'unknown' : $exit) . ').');
        }
        return trim($output);
    }

    /**
     * Atomic replace; all writes from this service happen under bootstrap.lock.
     * Other metadata writers should also coordinate via a shared locking policy.
     */
    private function updateMetadata(string $path, array $changes): void
    {
        $directory = dirname($path);
        $temporary = tempnam($directory, 'bootstrap-');
        if ($temporary === false) {
            throw new RuntimeException('Unable to create temporary metadata file.');
        }

        try {
            $metadata = array_merge($this->readMetadata($path), $changes);
            $json = json_encode($metadata,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            if (file_put_contents($temporary, $json, LOCK_EX) === false) {
                throw new RuntimeException('Unable to write server metadata.');
            }
            chmod($temporary, 0600);
            if (!rename($temporary, $path)) {
                throw new RuntimeException('Unable to replace server metadata.');
            }
        } finally {
            if (is_file($temporary)) {
                unlink($temporary);
            }
        }
    }
}
