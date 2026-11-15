
@extends('test.layouts.app')

@section('content')
    <style>
        .zo-enroll {
            max-width: 940px;
            margin: 45px auto 80px;
            color: #172b4d;
        }

        .zo-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .zo-header h1 {
            font-size: 32px;
            font-weight: 750;
        }

        .zo-header p {
            color: #718096;
            margin-top: 10px;
        }

        .zo-progress {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 28px;
        }

        .zo-progress-item {
            padding: 13px 8px;
            text-align: center;
            border-radius: 9px;
            background: #f1f5f9;
            color: #8290a4;
            font-size: 13px;
            font-weight: 600;
        }

        .zo-progress-item.complete {
            background: #e8f8ef;
            color: #17854a;
        }

        .zo-progress-item.active {
            background: #e7f1ff;
            color: #0969e8;
        }

        .zo-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 28px;
            margin-bottom: 22px;
            box-shadow: 0 6px 25px rgba(15, 35, 65, .05);
        }

        .zo-card h2 {
            font-size: 21px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .zo-muted {
            color: #718096;
            font-size: 14px;
            line-height: 1.8;
        }

        .zo-server-details {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 20px;
        }

        .zo-detail {
            background: #f4f7fb;
            border-radius: 9px;
            padding: 15px;
        }

        .zo-detail span {
            display: block;
            color: #8190a3;
            font-size: 12px;
            margin-bottom: 6px;
        }

        .zo-detail strong {
            font-size: 14px;
            overflow-wrap: anywhere;
        }

        .zo-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 30px;
            background: #fff4db;
            color: #996800;
            font-size: 12px;
            font-weight: 700;
        }

        .zo-badge.verified {
            background: #e5f8ed;
            color: #187a46;
        }

        .zo-terminal {
            background: #101d30;
            color: #a5e1ff;
            border-radius: 9px;
            padding: 17px;
            margin: 17px 0;
            overflow-x: auto;
        }

        .zo-terminal code {
            color: inherit;
            white-space: pre;
            font-size: 13px;
        }

        .zo-input {
            width: 100%;
            border: 1px solid #d7e0ec;
            border-radius: 8px;
            padding: 12px;
            min-height: 46px;
            margin-bottom: 15px;
        }

        .zo-input:focus {
            outline: none;
            border-color: #1675f9;
            box-shadow: 0 0 0 3px rgba(22,117,249,.12);
        }

        .zo-label {
            display: block;
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .zo-btn {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            border: 0;
            border-radius: 8px;
            background: #0969f6;
            color: #fff;
            padding: 12px 23px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .zo-btn:hover {
            background: #075bd3;
            color: #fff;
        }

        .zo-btn:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        .zo-notice {
            background: #eef5ff;
            border: 1px solid #d4e5ff;
            border-radius: 9px;
            padding: 15px;
            font-size: 13px;
            line-height: 1.8;
            color: #365477;
        }

        .zo-status {
            padding: 16px;
            border-radius: 9px;
            margin-top: 18px;
            font-size: 14px;
        }

        .zo-status.success {
            background: #e7f8ed;
            color: #176c40;
        }

        .zo-status.error {
            background: #fff0f0;
            color: #a63131;
        }

        .zo-status.loading {
            background: #eef5ff;
            color: #295e9c;
        }

        @media (max-width: 767px) {
            .zo-enroll {
                margin-top: 20px;
            }

            .zo-card {
                padding: 20px;
            }

            .zo-progress {
                grid-template-columns: repeat(2, 1fr);
            }

            .zo-server-details {
                grid-template-columns: 1fr;
            }

            .zo-btn {
                width: 100%;
            }
        }
    </style>

    @php
        $fingerprintVerified = !empty($server['host_fingerprint']);
    @endphp

    <div class="container zo-enroll">

        <header class="zo-header">
            <h1>Complete Server Setup</h1>

            <p>
                Welcome to ZeitOps!
                Your existing server enrollment has been restored.
            </p>
        </header>

        {{-- Progress --}}
        <div class="zo-progress">

            <div class="zo-progress-item complete">
                1. Server Details
            </div>

            <div class="zo-progress-item complete">
                2. Installation
            </div>

            <div class="zo-progress-item complete">
                3. Authentication
            </div>

            <div class="zo-progress-item active">
                4. Verification
            </div>

        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <p class="mb-1">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- Server Information --}}
        <section class="zo-card">

            <div class="d-flex justify-content-between align-items-center flex-wrap"
                 style="gap: 10px;">

                <h2>Your Server</h2>

                @if($fingerprintVerified)
                    <span class="zo-badge verified">
                    Host Identity Confirmed
                </span>
                @else
                    <span class="zo-badge">
                    Pending Verification
                </span>
                @endif

            </div>

            <p class="zo-muted">
                You are continuing the enrollment you created earlier.
                ZeitOps will reuse its existing SSH key pair.
            </p>

            <div class="zo-server-details">

                <div class="zo-detail">
                    <span>Server IP</span>
                    <strong>{{ $server['ip'] }}</strong>
                </div>

                <div class="zo-detail">
                    <span>SSH Port</span>
                    <strong>{{ $server['port'] }}</strong>
                </div>

                <div class="zo-detail">
                    <span>SSH Username</span>
                    <strong>zeitops</strong>
                </div>

            </div>

            <div class="mt-3">
                <small class="text-muted">
                    Enrollment ID: {{ $serverId }}
                </small>
            </div>

        </section>

        {{-- Verify Host Fingerprint --}}
        <section class="zo-card">

            <h2>Verify Server Identity</h2>

            <p class="zo-muted">
                Before ZeitOps connects to your VPS,
                confirm its SSH host fingerprint using
                a trusted source such as your VPS provider console.
            </p>

            <label class="zo-label">
                Run this command on your VPS
            </label>

            <div class="zo-terminal">
                <code id="fingerprint-command">ssh-keygen -lf /etc/ssh/ssh_host_ed25519_key.pub</code>
            </div>

            <button
                type="button"
                id="copy-fingerprint-command"
                class="zo-btn"
            >
                Copy Command
            </button>

            <p class="zo-muted mt-3">
                The result contains a value beginning with
                <strong>SHA256:</strong>.
                Copy only that fingerprint.
                Do not use the fingerprint of the ZeitOps
                authentication key.
            </p>

            @if(!$fingerprintVerified)

                <form
                    method="POST"
                    action="{{ route('servers.verify-host', ['serverId' => $serverId]) }}"
                >
                    @csrf

                    <label for="fingerprint" class="zo-label">
                        SSH Host Fingerprint
                    </label>

                    <input
                        type="text"
                        id="fingerprint"
                        name="fingerprint"
                        class="zo-input"
                        placeholder="SHA256:..."
                        value="{{ old('fingerprint') }}"
                        autocomplete="off"
                        required
                    >

                    <button type="submit" class="zo-btn">
                        Confirm Server Identity
                    </button>

                </form>

            @else

                <div class="zo-notice mt-3">
                    <strong>Verified fingerprint</strong>

                    <div style="overflow-wrap: anywhere;">
                        {{ $server['host_fingerprint'] }}
                    </div>
                </div>

            @endif

        </section>

        {{-- SSH Check --}}
        <section class="zo-card">

            <h2>Check SSH Access</h2>

            <p class="zo-muted">
                ZeitOps will attempt to authenticate using the
                unique SSH private key generated for this enrollment.
            </p>

            @if(!$fingerprintVerified)

                <div class="zo-notice">
                    Confirm the SSH host fingerprint above
                    before checking access.
                </div>

                <button
                    type="button"
                    class="zo-btn mt-3"
                    disabled
                >
                    Check Server Access
                </button>

            @else

                <button
                    type="button"
                    id="check-access"
                    class="zo-btn"
                >
                    Check Server Access
                </button>

                <div
                    id="check-result"
                    role="status"
                    aria-live="polite"
                ></div>

            @endif

        </section>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const copyButton = document.getElementById(
                'copy-fingerprint-command'
            );

            if (copyButton) {

                copyButton.addEventListener('click', async () => {

                    const command = document.getElementById(
                        'fingerprint-command'
                    ).textContent.trim();

                    try {
                        await navigator.clipboard.writeText(command);

                        copyButton.textContent = 'Copied!';

                        setTimeout(() => {
                            copyButton.textContent = 'Copy Command';
                        }, 2000);

                    } catch (error) {
                        copyButton.textContent = 'Copy Failed';
                    }
                });
            }

            const checkButton = document.getElementById('check-access');
            const checkResult = document.getElementById('check-result');

            if (!checkButton || !checkResult) {
                return;
            }

            checkButton.addEventListener('click', async () => {

                checkButton.disabled = true;
                checkButton.textContent = 'Checking...';

                checkResult.className = 'zo-status loading';
                checkResult.textContent =
                    'Connecting to your server securely...';

                try {

                    const response = await fetch(
                        @json(route('servers.check', ['serverId' => $serverId])),
                        {
                            method: 'GET',
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'X-Enrollment-Token': @json($enrollmentToken)
                            }
                        }
                    );

                    const data = await response.json();

                    if (
                        response.ok &&
                        (data.success === true || data.ssh?.ok === true)
                    ) {

                        checkResult.className = 'zo-status success';

                        checkResult.textContent =
                            'SSH authentication successful! ' +
                            'Your server is connected to ZeitOps.';

                    } else {

                        checkResult.className = 'zo-status error';

                        checkResult.textContent =
                            data.message ||
                            'Unable to verify SSH connection.';

                    }

                } catch (error) {

                    checkResult.className = 'zo-status error';

                    checkResult.textContent =
                        'Connection check failed. Please try again.';

                } finally {

                    checkButton.disabled = false;
                    checkButton.textContent = 'Check Server Access';

                }
            });

        });
    </script>

@endsection
