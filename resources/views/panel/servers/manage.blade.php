@extends('panel.layouts.master')

@section('wrapper')

    @php
        $status = strtolower(
            (string) ($server['status'] ?? 'unknown')
        );

        $serverName = $server['name']
            ?? $server['server_name']
            ?? 'Server ' . substr($serverId, 0, 8);

        $serverIp = $server['ip']
            ?? $server['ip_address']
            ?? 'Unknown';

        $sshPort = $server['port'] ?? 22;

        $sshUser = $server['username']
            ?? $server['ssh_user']
            ?? 'zeitops';

        $hostVerified = !empty($server['host_fingerprint']);
        $provisioningStatus = strtolower((string) ($server['provisioning_status'] ?? 'not_configured'));
        $bootstrapVersion = $server['bootstrap_version'] ?? null;
        $bootstrapCompletedAt = $server['bootstrap_completed_at'] ?? null;
        $isReady = $provisioningStatus === 'ready';
    @endphp

    <style>
        .zo-manage-page {
            min-height: calc(100vh - 75px);
            padding: 28px;
            background: #f5f7fc;
            color: #263750;
        }

        .zo-manage-container {
            max-width: 1150px;
            margin: 0 auto;
        }

        .zo-manage-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 22px;
            color: #7485a1;
            font-size: 12px;
            text-decoration: none;
        }

        .zo-manage-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        .zo-manage-heading h1 {
            font-size: 28px;
            font-weight: 750;
            color: #20314c;
            margin: 0 0 8px;
        }

        .zo-manage-heading p {
            color: #8191a8;
            font-size: 13px;
            margin: 0;
        }

        .zo-manage-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 13px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 700;
        }

        .zo-manage-badge.connected {
            background: #e4f8ee;
            color: #168956;
        }

        .zo-manage-badge.other {
            background: #fff2db;
            color: #ae781b;
        }

        .zo-manage-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .zo-manage-card {
            background: #fff;
            border: 1px solid #e5eaf4;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 18px rgba(24, 45, 90, .035);
        }

        .zo-manage-card-head {
            padding: 20px 22px;
            border-bottom: 1px solid #edf0f6;
        }

        .zo-manage-card-head h2 {
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 6px;
            color: #253550;
        }

        .zo-manage-card-head p {
            margin: 0;
            font-size: 12px;
            color: #8d9aae;
        }

        .zo-manage-card-body {
            padding: 22px;
        }

        .zo-manage-row {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding: 13px 0;
            border-bottom: 1px solid #f0f2f7;
            font-size: 12px;
        }

        .zo-manage-row:last-child {
            border-bottom: 0;
        }

        .zo-manage-row span {
            color: #8694a9;
        }

        .zo-manage-row strong {
            color: #344660;
            overflow-wrap: anywhere;
        }

        .zo-feature {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 15px;
            margin-bottom: 12px;
            border: 1px solid #e8edf6;
            border-radius: 10px;
        }

        .zo-feature-icon {
            width: 38px;
            height: 38px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eaf0ff;
            color: #3869ea;
            border-radius: 10px;
        }

        .zo-feature h3 {
            margin: 0 0 6px;
            color: #2c3c57;
            font-size: 13px;
            font-weight: 700;
        }

        .zo-feature p {
            margin: 0;
            color: #8593a8;
            font-size: 11px;
            line-height: 1.7;
        }

        .zo-manage-notice {
            margin-top: 18px;
            padding: 15px;
            border-radius: 10px;
            background: #edf4ff;
            color: #4b6e9f;
            font-size: 12px;
            line-height: 1.8;
        }

        .zo-manage-footer {
            margin-top: 23px;
        }

        .zo-manage-button {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 11px 17px;
            background: linear-gradient(135deg,#2477ff,#4852e2);
            border-radius: 9px;
            color: white;
            font-weight: 650;
            font-size: 12px;
            text-decoration: none;
        }

        .zo-manage-button:hover {
            color: white;
        }

        .zo-bootstrap-panel { margin-top: 20px; }
        .zo-bootstrap-status {
            display: inline-flex; align-items: center; gap: 8px;
            border-radius: 999px; padding: 8px 13px;
            font-size: 12px; font-weight: 700;
            background: #fff4db; color: #ad7615;
        }
        .zo-bootstrap-status.ready { background: #e4f8ee; color: #168956; }
        .zo-bootstrap-status.failed { background: #fff0f2; color: #c43f59; }
        .zo-bootstrap-description { font-size: 13px; color: #64748b; line-height: 1.8; }
        .zo-bootstrap-label { color: #344660; font-size: 12px; font-weight: 650; }
        .zo-bootstrap-input { border: 1px solid #dce5f2; border-radius: 9px; padding: 11px 13px; }
        .zo-bootstrap-input:focus { border-color: #4882ff; box-shadow: 0 0 0 3px rgba(72,130,255,.12); }
        .zo-manage-button { border: 0; cursor: pointer; }
        .zo-manage-button:disabled { opacity: .6; cursor: not-allowed; }
        .zo-bootstrap-summary { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; margin-bottom: 15px; }
        .zo-bootstrap-help { font-size: 12px; color: #7d8ea5; line-height: 1.7; }

        @media(max-width: 750px) {
            .zo-manage-page {
                padding: 16px;
            }

            .zo-manage-grid {
                grid-template-columns: 1fr;
            }

            .zo-manage-heading h1 {
                font-size: 23px;
            }
        }
    </style>

    <div class="zo-manage-page">
        <div class="zo-manage-container">

            <a
                href="{{ route('panel.servers.all') }}"
                class="zo-manage-back"
            >
                <i class="fas fa-arrow-left"></i>
                Back to All Servers
            </a>

            <div class="zo-manage-heading">

                <div>
                    <h1>{{ $serverName }}</h1>

                    <p>
                        Manage your VPS infrastructure,
                        connectivity, and server configuration.
                    </p>
                </div>

                <span class="zo-manage-badge {{ $status === 'connected' ? 'connected' : 'other' }}">
                <i class="fas {{ $status === 'connected' ? 'fa-check-circle' : 'fa-clock' }}"></i>
                {{ ucfirst($status) }}
            </span>

            </div>

            <div class="zo-manage-grid">

                {{-- SERVER INFORMATION --}}
                <section class="zo-manage-card">

                    <div class="zo-manage-card-head">
                        <h2>
                            <i class="fas fa-server me-2"></i>
                            Server Information
                        </h2>
                        <p>Registered VPS connection details</p>
                    </div>

                    <div class="zo-manage-card-body">

                        <div class="zo-manage-row">
                            <span>Server ID</span>
                            <strong>{{ $serverId }}</strong>
                        </div>

                        <div class="zo-manage-row">
                            <span>IP Address</span>
                            <strong>{{ $serverIp }}</strong>
                        </div>

                        <div class="zo-manage-row">
                            <span>SSH Port</span>
                            <strong>{{ $sshPort }}</strong>
                        </div>

                        <div class="zo-manage-row">
                            <span>SSH Username</span>
                            <strong>{{ $sshUser }}</strong>
                        </div>

                        <div class="zo-manage-row">
                            <span>Host Identity</span>
                            <strong>
                                @if($hostVerified)
                                    <span style="color:#168956;">
                                    <i class="fas fa-shield-alt"></i>
                                    Verified
                                </span>
                                @else
                                    <span style="color:#b77a1a;">
                                    Unverified
                                </span>
                                @endif
                            </strong>
                        </div>

                        <div class="zo-manage-row">
                            <span>Last Connected</span>
                            <strong>
                                {{ $server['last_connected_at'] ?? 'Never' }}
                            </strong>
                        </div>

                    </div>

                </section>

                {{-- MANAGEMENT FEATURES --}}
                <section class="zo-manage-card">

                    <div class="zo-manage-card-head">
                        <h2>
                            <i class="fas fa-cogs me-2"></i>
                            Server Management
                        </h2>
                        <p>Infrastructure configuration and preparation</p>
                    </div>

                    <div class="zo-manage-card-body">

                        <div class="zo-feature">
                            <div class="zo-feature-icon">
                                <i class="fas fa-microchip"></i>
                            </div>

                            <div>
                                <h3>System Inspection</h3>
                                <p>
                                    Detect the operating system,
                                    CPU, RAM, storage, and installed software.
                                </p>
                            </div>
                        </div>

                        <div class="zo-feature">
                            <div class="zo-feature-icon">
                                <i class="fas fa-layer-group"></i>
                            </div>

                            <div>
                                <h3>Technology Stacks</h3>
                                <p>
                                    Inspect available web servers,
                                    runtimes, and database services.
                                </p>
                            </div>
                        </div>

                        <div class="zo-feature">
                            <div class="zo-feature-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>

                            <div>
                                <h3>Security & Access</h3>
                                <p>
                                    Review SSH security and permissions
                                    required for future provisioning.
                                </p>
                            </div>
                        </div>

                        <div class="zo-manage-notice">
                            <i class="fas fa-info-circle me-2"></i>

                            @if($isReady)
                                This VPS has completed management bootstrap. Website deployment
                                requires the separate stack and application provisioning workflow.
                            @else
                                SSH connectivity alone does not grant installation permissions.
                                Complete Automated Server Preparation below to enable the restricted
                                management helper.
                            @endif
                        </div>

                    </div>

                </section>

            </div>


            {{-- AUTOMATED SERVER BOOTSTRAP --}}
            <section class="zo-manage-card zo-bootstrap-panel" aria-labelledby="zo-bootstrap-heading">
                <div class="zo-manage-card-head">
                    <h2 id="zo-bootstrap-heading">
                        <i class="fas fa-shield-alt me-2"></i>
                        Automated Server Preparation
                    </h2>
                    <p>Authorize ZeitOps to prepare this VPS for website deployments</p>
                </div>

                <div class="zo-manage-card-body">
                    @if(session('success'))
                        <div class="alert alert-success" role="status">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ $errors->first('bootstrap') ?: $errors->first() }}
                        </div>
                    @endif

                    <div class="zo-bootstrap-summary">
                        <span class="zo-bootstrap-status {{ $isReady ? 'ready' : ($provisioningStatus === 'failed' ? 'failed' : '') }}">
                            <i class="fas {{ $isReady ? 'fa-check-circle' : ($provisioningStatus === 'failed' ? 'fa-times-circle' : 'fa-clock') }}"></i>
                            {{ ucwords(str_replace('_', ' ', $provisioningStatus)) }}
                        </span>
                        @if($bootstrapVersion)
                            <span class="zo-bootstrap-help">Bootstrap version: {{ $bootstrapVersion }}</span>
                        @endif
                        @if($bootstrapCompletedAt)
                            <span class="zo-bootstrap-help">Completed: {{ $bootstrapCompletedAt }}</span>
                        @endif
                    </div>

                    @if($isReady)
                        <p class="zo-bootstrap-description">
                            The restricted ZeitOps management helper has been installed and verified.
                            You can continue to website provisioning.
                        </p>
                        <a href="{{ route('panel.websites.all') }}" class="zo-manage-button">
                            <i class="fas fa-globe"></i> View Websites
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    @elseif($status !== 'connected')
                        <div class="alert alert-warning mb-0" role="status">
                            Verify the server's SSH connection and trusted host fingerprint before bootstrapping.
                        </div>
                    @elseif(!Route::has('panel.servers.bootstrap'))
                        <div class="alert alert-warning mb-0" role="status">
                            The server bootstrap endpoint is not configured yet.
                        </div>
                    @else
                        <p class="zo-bootstrap-description">
                            ZeitOps will use one-time administrator authorization to create or update the
                            management account, install its restricted helper, configure the approved sudo
                            policy and verify the result. Back up your VPS and review these changes before proceeding.
                        </p>

                        {{-- ALTERNATIVE: ZEITOPS-GENERATED TEMPORARY ADMINISTRATOR KEY --}}
                        @php
                            $generatedKeyPath = storage_path("app/private/zeitops/servers/{$serverId}/bootstrap_admin_ed25519");
                            $generatedPublicPath = $generatedKeyPath . '.pub';
                            $generatedKeyValid = is_file($generatedKeyPath)
                                && is_file($generatedPublicPath)
                                && !is_link($generatedKeyPath)
                                && !is_link($generatedPublicPath)
                                && time() - filemtime($generatedKeyPath) < 1800;
                            $generatedPublicKey = $generatedKeyValid
                                ? trim(file_get_contents($generatedPublicPath)) : null;
                            $installKeyCommand = $generatedPublicKey
                                ? 'umask 077; mkdir -p /root/.ssh; touch /root/.ssh/authorized_keys; chmod 700 /root/.ssh; chmod 600 /root/.ssh/authorized_keys; KEY=' . escapeshellarg($generatedPublicKey)
                                    . '; grep -qxF -- "$KEY" /root/.ssh/authorized_keys || printf "%s\n" "$KEY" >> /root/.ssh/authorized_keys'
                                : null;
                        @endphp
                        <div class="zo-feature" style="display:block; margin-bottom:24px;">
                            <h3><i class="fas fa-key me-2"></i> Option 2 — Generate a temporary administrator key</h3>
                            <p style="margin:8px 0 14px;">Don't want to upload your private key? ZeitOps generates a separate temporary SSH key. Authorize its public key once in your VPS provider's root console. ZeitOps removes the authorized key after a successful bootstrap.</p>
                            @if(Route::has('panel.servers.bootstrap-key.generate'))
                                <form method="POST" action="{{ route('panel.servers.bootstrap-key.generate', ['serverId' => $serverId]) }}">
                                    @csrf
                                    <button type="submit" class="zo-manage-button" style="border:0;">Generate Temporary Administrator Key</button>
                                </form>
                            @else
                                <p class="text-danger">Temporary-key route is not registered.</p>
                            @endif
                            @if($generatedPublicKey)
                                <label class="form-label mt-3">Generated public key (safe to share)</label>
                                <textarea class="form-control" rows="3" readonly>{{ $generatedPublicKey }}</textarea>
                                <label class="form-label mt-3">Run this one-time command in the VPS root web console</label>
                                <textarea class="form-control" rows="5" readonly id="zo-root-authorization-command">{{ $installKeyCommand }}</textarea>
                                <button type="button" class="btn btn-outline-primary mt-2" onclick="navigator.clipboard.writeText(document.getElementById('zo-root-authorization-command').value)">Copy Console Command</button>
                                <div class="alert alert-warning mt-3">This public key expires in 30 minutes on ZeitOps. If bootstrap fails, remove the temporary key from root's authorized_keys using the VPS console. Do not put any private keys in the console command.</div>
                                <form method="POST" action="{{ route('panel.servers.bootstrap', ['serverId' => $serverId]) }}" class="mt-3">
                                    @csrf
                                    <input type="hidden" name="authorization_method" value="generated_key">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="zo-confirm-generated" name="confirm_bootstrap" value="1" required>
                                        <label class="form-check-label" for="zo-confirm-generated">I authorized this key on my VPS and approve installation of the restricted management helper.</label>
                                    </div>
                                    <button class="zo-manage-button" type="submit" style="border:0;">Verify Key &amp; Bootstrap VPS</button>
                                </form>
                            @endif
                        </div>

                        <h3 class="mb-3" style="font-size:16px;">Option 1 — Use your existing administrator private key</h3>
                        <form method="POST"
                              action="{{ route('panel.servers.bootstrap', ['serverId' => $serverId]) }}"
                              autocomplete="off"
                              id="zo-bootstrap-form">
                            @csrf
                            <input type="hidden" name="authorization_method" value="existing_key">

                            <div class="mb-3">
                                <label for="zo-admin-username" class="form-label zo-bootstrap-label">
                                    Administrator SSH Username
                                </label>
                                <input id="zo-admin-username" type="text" name="admin_username"
                                       class="form-control zo-bootstrap-input @error('admin_username') is-invalid @enderror"
                                       value="{{ old('admin_username', 'root') }}"
                                       autocomplete="off" maxlength="32" required>
                                @error('admin_username')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="zo-admin-key" class="form-label zo-bootstrap-label">
                                    Administrator SSH Private Key
                                </label>
                                <textarea id="zo-admin-key" name="admin_private_key"
                                          class="form-control zo-bootstrap-input @error('admin_private_key') is-invalid @enderror"
                                          rows="8" maxlength="20000" spellcheck="false"
                                          autocomplete="off" required
                                          placeholder="-----BEGIN OPENSSH PRIVATE KEY-----"></textarea>
                                @error('admin_private_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="zo-bootstrap-help mt-2">
                                    Paste an existing administrator SSH private key that the VPS trusts.
                                    This key is used only for bootstrap; it is not saved in server.json.
                                    Never paste it into chat or logs.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="zo-admin-passphrase" class="form-label zo-bootstrap-label">
                                    Key Passphrase (if encrypted)
                                </label>
                                <input id="zo-admin-passphrase" type="password"
                                       name="admin_key_passphrase" class="form-control zo-bootstrap-input"
                                       autocomplete="off" maxlength="4096">
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="confirm_bootstrap"
                                       value="1" id="zo-bootstrap-confirm" required>
                                <label class="form-check-label zo-bootstrap-description" for="zo-bootstrap-confirm">
                                    I own or administer this VPS and authorize ZeitOps to install its
                                    management account, helper and restricted privileged operations.
                                </label>
                            </div>

                            <button type="submit" class="zo-manage-button" id="zo-bootstrap-submit">
                                <i class="fas fa-shield-alt"></i>
                                <span id="zo-bootstrap-submit-label">Authorize &amp; Prepare Server</span>
                            </button>
                        </form>
                    @endif
                </div>
            </section>

            <div class="zo-manage-footer">
                <a
                    href="{{ route('panel.websites.all') }}"
                    class="zo-manage-button"
                >
                    <i class="fas fa-globe"></i>
                    View My Websites
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>

        </div>
    </div>

    <script>
        document.getElementById('zo-bootstrap-form')?.addEventListener('submit', function (event) {
            if (!this.reportValidity()) {
                event.preventDefault();
                return;
            }
            const submit = document.getElementById('zo-bootstrap-submit');
            if (submit) {
                submit.disabled = true;
                document.getElementById('zo-bootstrap-submit-label').textContent = 'Preparing Server...';
            }
        });
    </script>
@endsection
