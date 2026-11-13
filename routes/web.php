<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;
use Illuminate\Support\Facades\Process;

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
        'ip' => 'required|ip',
        'port' => 'required|integer|between:1,65535',
        'username' => 'required|string',
    ]);

    $ip = $validated['ip'];

    // ZeitOps SSH public key
    $publicKey = 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQCuAF62X1gV6++y2/5NLHlKdYD4lMY74p8no0aUEbLqCxebSRzsoKIpOY7W1jk9vRjQweCjB0gKeLT8Kj9k4Jn7gBsDBahWF40OMK1JbpvhqjX6qXhhKj1CmXGG5rhhhuzzuhKeWzq8lV5Pc7pFbMLDuQSjT9Cehfj59T6xio0o+0YEzJPO2N0dljExynaTmiZJ+6a+PDVPbOjQpMtAdwkc8NnBJ38/SMy07ejCCXDOH1SSjcBum0hSlMtBXTylZiVuUrYckERYSBHvcg0z0Omlfek3y3uyMLGpPyL0wsV5b/snx87g3G53aGPkWoRh1hiILHFR38a7u9n/uuo6O8TmQXN3DIJR/IDdkaswQo8CExuiYPid2/UzzYDfEdzMz/lDrx+dJ1Vc/jVLsPJuMJNTTmhzGokBE0u5J9eIQ7kWPH6Sgthl6K00s15GH3/R3kmQV4xuUc7EatWonLGSkr+1nihuMk8ZLRR3wkKPhptos4Onv37CjQJVe4LbYT+HMTVNBS71Sebg/fAkcoN21lTDTMEWBPdUoKTQ/yMqave1Veu13PUT6BI+v48Ce+symsiPkUni3hXsXBnKkHnWxKsI3GjJ9f8Wrc7j16GwaHKWDW0A374QVcdXCirF6sHGUmONKLgqDcdyU/AbqLMEUXbtBkGQZL06mz8QIlxtcVPvGw== zeitops@hackalone';

    $command = <<<EOT
#!/usr/bin/env bash
set -euo pipefail

# ==========================================
# ZeitOps - Server Enrollment
# Website: https://zeitops.eu
# ==========================================

# 1. Ensure administrator privileges
if [ "\$(id -u)" -ne 0 ]; then
    echo "Please execute this script using sudo."
    exit 1
fi

# 2. Create the ZeitOps user if missing
if ! id -u zeitops >/dev/null 2>&1; then
    useradd -m -s /bin/bash zeitops
    echo "ZeitOps user created."
else
    echo "ZeitOps user already exists."
fi

# 3. Get the actual home directory
ZEITOPS_HOME="\$(getent passwd zeitops | cut -d: -f6)"

if [ -z "\$ZEITOPS_HOME" ] || [ ! -d "\$ZEITOPS_HOME" ]; then
    echo "Invalid ZeitOps home directory."
    exit 1
fi

# 4. Create SSH directory with secure permissions
install -d -m 700 -o zeitops -g zeitops "\$ZEITOPS_HOME/.ssh"

# 5. Configure authorized_keys
AUTHORIZED_KEYS="\$ZEITOPS_HOME/.ssh/authorized_keys"

touch "\$AUTHORIZED_KEYS"
chown zeitops:zeitops "\$AUTHORIZED_KEYS"
chmod 600 "\$AUTHORIZED_KEYS"

# 6. Install public key only if missing
PUBLIC_KEY='$publicKey'

if ! grep -qxF "\$PUBLIC_KEY" "\$AUTHORIZED_KEYS"; then
    printf '%s\\n' "\$PUBLIC_KEY" >> "\$AUTHORIZED_KEYS"
    echo "ZeitOps SSH public key installed."
else
    echo "ZeitOps SSH public key already exists."
fi

# 7. Verify SSH directory permissions
chown zeitops:zeitops "\$ZEITOPS_HOME/.ssh"
chmod 700 "\$ZEITOPS_HOME/.ssh"
chmod 600 "\$AUTHORIZED_KEYS"

# 8. Display result
echo ""
echo "=========================================="
echo "ZeitOps enrollment completed successfully."
echo "=========================================="
echo "User: zeitops"
echo "SSH directory: \$ZEITOPS_HOME/.ssh"
echo ""
echo "Return to https://zeitops.eu"
echo "and click Check Access."
EOT;

    return view('test.new_server', compact(
        'validated',
        'command',
        'ip'
    ));

})->name('servers.store');


Route::get('/servers/new/{ip}', function (string $ip) {
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

    return view('test.check', [
        'ip' => $ip,
        'pingStatus' => $pingStatus,
        'pingOutput' => $pingOutput,
        'sshStatus' => $sshStatus,
    ]);
});
