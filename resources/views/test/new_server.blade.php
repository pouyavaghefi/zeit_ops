@extends('test.layouts.app')

@section('content')
    <style>
        .zo-page {
            max-width: 1050px;
            margin: 45px auto 80px;
            color: #14233f;
        }

        .zo-heading {
            text-align: center;
            margin-bottom: 35px;
        }

        .zo-heading h1 {
            font-size: 34px;
            font-weight: 750;
            margin-bottom: 12px;
        }

        .zo-heading p {
            color: #718096;
            font-size: 16px;
        }

        .zo-steps {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 30px;
        }

        .zo-step {
            text-align: center;
            padding: 14px 8px;
            border-radius: 10px;
            background: #f1f5fa;
            color: #8490a4;
            font-size: 13px;
            font-weight: 600;
        }

        .zo-step.active {
            background: #e8f2ff;
            color: #0869e9;
        }

        .zo-step.done {
            background: #e8f8ef;
            color: #16804a;
        }

        .zo-step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: #ffffff;
            margin-right: 5px;
        }

        .zo-card {
            background: #ffffff;
            border: 1px solid #e4eaf2;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 7px 24px rgba(16, 36, 68, .05);
            margin-bottom: 22px;
        }

        .zo-card-title {
            font-size: 21px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .zo-description {
            color: #718096;
            font-size: 14px;
            line-height: 1.8;
        }

        .zo-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .zo-input {
            width: 100%;
            min-height: 46px;
            border: 1px solid #d9e1eb;
            border-radius: 8px;
            padding: 10px 13px;
            background: #ffffff;
            color: #14233f;
        }

        .zo-input:focus {
            border-color: #1478ff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(20,120,255,.12);
        }

        .zo-btn {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            min-height: 44px;
            border-radius: 8px;
            padding: 11px 21px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: .2s;
            border: 0;
        }

        .zo-btn-primary {
            color: #ffffff;
            background: #0969f6;
        }

        .zo-btn-primary:hover {
            color: #ffffff;
            background: #075bd3;
        }

        .zo-btn-outline {
            color: #176ce5;
            background: #ffffff;
            border: 1px solid #bcd6ff;
        }

        .zo-btn-outline:hover {
            background: #eef5ff;
        }

        .zo-btn:disabled {
            opacity: .6;
            cursor: not-allowed;
        }

        .zo-command-box {
            background: #101c2f;
            color: #a8e2ff;
            border-radius: 10px;
            padding: 18px;
            margin: 18px 0;
            overflow-x: auto;
        }

        .zo-command-box code {
            display: block;
            color: inherit;
            font-size: 12px;
            line-height: 1.8;
            white-space: pre;
            overflow-wrap: normal;
            user-select: all;
        }

        .zo-notice {
            padding: 15px;
            background: #f0f6ff;
            border: 1px solid #cfe1ff;
            border-radius: 9px;
            font-size: 13px;
            line-height: 1.7;
            color: #365278;
        }

        .zo-server-summary {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin: 18px 0;
        }

        .zo-summary-item {
            background: #f4f7fb;
            border-radius: 8px;
            padding: 12px 17px;
            font-size: 13px;
        }

        .zo-summary-item strong {
            display: block;
            margin-top: 4px;
            color: #162847;
        }

        .zo-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
        }

        .zo-code-hint {
            color: #7d8ca3;
            font-size: 12px;
            margin-top: 10px;
        }

        .zo-auth-panel {
            background: #f8fbff;
            border: 1px solid #dce9fa;
            border-radius: 11px;
            padding: 25px;
            text-align: center;
        }

        .zo-auth-panel h3 {
            font-size: 20px;
            font-weight: 700;
        }

        .zo-auth-panel p {
            color: #73839b;
            font-size: 14px;
            margin: 12px 0 20px;
        }

        .zo-auth-buttons {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
        }

        .zo-result {
            margin-top: 20px;
            padding: 17px;
            border-radius: 9px;
            font-size: 14px;
            line-height: 1.7;
        }

        .zo-result-success {
            background: #e7f8ef;
            border: 1px solid #a9dfc0;
            color: #17643c;
        }

        .zo-result-error {
            background: #fff0f0;
            border: 1px solid #f0c0c0;
            color: #a12e2e;
        }

        .zo-result-loading {
            background: #eef5ff;
            border: 1px solid #cfe0ff;
            color: #245c9d;
        }

        @media (max-width: 767px) {
            .zo-page {
                margin-top: 20px;
            }

            .zo-heading h1 {
                font-size: 27px;
            }

            .zo-steps {
                grid-template-columns: repeat(2, 1fr);
            }

            .zo-card {
                padding: 20px;
            }

            .zo-btn {
                width: 100%;
            }

            .zo-actions {
                flex-direction: column;
            }
        }
    </style>

    <main class="container zo-page">

        <header class="zo-heading">
            <h1>Connect Your Server</h1>
            <p>
                Connect your VPS to ZeitOps and manage your
                infrastructure from one place.
            </p>
        </header>

        {{-- Onboarding progress --}}
        <div class="zo-steps">

            <div class="zo-step {{ isset($command) ? 'done' : 'active' }}">
                <span class="zo-step-number">1</span>
                Server Details
            </div>

            <div class="zo-step {{ isset($command) ? 'active' : '' }}">
                <span class="zo-step-number">2</span>
                Install ZeitOps
            </div>

            <div class="zo-step {{ isset($command) ? (auth()->check() ? 'done' : 'active') : '' }}">
                <span class="zo-step-number">3</span>
                Authentication
            </div>

            <div class="zo-step {{ isset($command) && auth()->check() ? 'active' : '' }}">
                <span class="zo-step-number">4</span>
                Verify Access
            </div>

        </div>

        {{-- STEP 1: SERVER DETAILS --}}
        @unless(isset($command))

            <section class="zo-card">

                <h2 class="zo-card-title">Add a New Server</h2>

                <p class="zo-description">
                    Enter your VPS details to generate a secure,
                    server-specific installation command.
                    Your root password is not required.
                </p>

                @if($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <strong>Please review the following:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form
                    method="POST"
                    action="{{ route('servers.store') }}"
                    id="server-create-form"
                >
                    @csrf

                    <div class="mb-4">
                        <label for="type" class="zo-label">
                            Server Type
                        </label>

                        <select
                            name="type"
                            id="type"
                            class="zo-input"
                            required
                        >
                            <option value="">Select a server configuration</option>

                            <option value="1" @selected(old('type') == '1')>
                                Web Full — Nginx, PHP, Node.js, Redis, Database
                            </option>

                            <option value="2" @selected(old('type') == '2')>
                                Web NoDB — Nginx, Node.js, PHP
                            </option>

                            <option value="3" @selected(old('type') == '3')>
                                Email Server — WildDuck
                            </option>

                            <option value="4" @selected(old('type') == '4')>
                                Worker — Node.js, PHP
                            </option>

                            <option value="5" @selected(old('type') == '5')>
                                Database Server
                            </option>

                            <option value="6" @selected(old('type') == '6')>
                                Cache — Memcached, Redis
                            </option>

                            <option value="7" @selected(old('type') == '7')>
                                Load Balancer — Nginx
                            </option>

                            <option value="8" @selected(old('type') == '8')>
                                Meilisearch — Search Server
                            </option>
                        </select>
                    </div>

                    <div class="row">

                        <div class="col-md-8 mb-4">
                            <label for="ip" class="zo-label">
                                Server IPv4 Address
                            </label>

                            <input
                                type="text"
                                name="ip"
                                id="ip"
                                class="zo-input"
                                placeholder="203.0.113.10"
                                value="{{ old('ip', request('ip')) }}"
                                autocomplete="off"
                                required
                            >
                        </div>

                        <div class="col-md-4 mb-4">
                            <label for="port" class="zo-label">
                                SSH Port
                            </label>

                            <input
                                type="number"
                                name="port"
                                id="port"
                                class="zo-input"
                                min="1"
                                max="65535"
                                value="{{ old('port', request('port', 22)) }}"
                                required
                            >
                        </div>

                    </div>

                    {{-- Kept for compatibility with the existing route.
                         The generated installer uses the zeitops account. --}}
                    <input
                        type="hidden"
                        name="username"
                        value="zeitops"
                    >

                    <div class="zo-notice mb-4">
                        <strong>Secure onboarding</strong>
                        <div>
                            ZeitOps generates a dedicated SSH key pair
                            for your server. You install its public key
                            using a command on your VPS. Your existing
                            root password is never requested.
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="zo-btn zo-btn-primary"
                        id="generate-command-btn"
                    >
                        Generate Setup Command
                    </button>

                </form>

            </section>

        @endunless

        {{-- STEP 2: INSTALLATION --}}
        @isset($command)

            <section class="zo-card">

                <h2 class="zo-card-title">
                    Your Server Setup Command
                </h2>

                <p class="zo-description">
                    Your enrollment has been created.
                    Execute the command below on the server
                    you want to connect to ZeitOps.
                </p>

                <div class="zo-server-summary">

                    <div class="zo-summary-item">
                        Server IP
                        <strong>{{ $ip }}</strong>
                    </div>

                    <div class="zo-summary-item">
                        SSH Port
                        <strong>{{ $validated['port'] }}</strong>
                    </div>

                    <div class="zo-summary-item">
                        SSH User
                        <strong>zeitops</strong>
                    </div>

                    <div class="zo-summary-item">
                        Status
                        <strong>Pending Enrollment</strong>
                    </div>

                </div>

                <label for="zeitops-command" class="zo-label">
                    Run this on your VPS
                </label>

                <div class="zo-command-box">
                    <code id="zeitops-command">{{ $command }}</code>
                </div>

                <div class="zo-actions">
                    <button
                        type="button"
                        class="zo-btn zo-btn-primary"
                        id="copy-command"
                    >
                        Copy Command
                    </button>
                </div>

                <div
                    id="copy-feedback"
                    class="zo-code-hint"
                    role="status"
                    aria-live="polite"
                >
                    Copy the full command and run it as an administrator.
                </div>

                <div class="zo-notice mt-4">
                    <strong>Installation instructions</strong>

                    <ol class="mb-0 mt-2">
                        <li>Connect to your VPS using SSH or your provider console.</li>
                        <li>Paste and execute the generated command.</li>
                        <li>Confirm that ZeitOps reports successful installation.</li>
                        <li>Return to this page to continue verification.</li>
                    </ol>
                </div>

            </section>

            {{-- STEP 3: AUTHENTICATION --}}
            <section class="zo-card">

                <h2 class="zo-card-title">
                    Secure Your Server
                </h2>

                @guest

                    <div class="zo-auth-panel">

                        <h3>Sign in to continue</h3>

                        <p>
                            Your setup command has been generated.
                            Create a ZeitOps account or sign in
                            to continue with server verification
                            and management.
                        </p>

                        <div class="zo-auth-buttons">

                            <a
                                href="{{ route('register') }}"
                                class="zo-btn zo-btn-primary"
                            >
                                Create Account
                            </a>

                            <a
                                href="{{ route('login') }}"
                                class="zo-btn zo-btn-outline"
                            >
                                Sign In
                            </a>

                        </div>

                    </div>

                    <p class="zo-code-hint mt-3">
                        Your enrollment must be preserved during
                        authentication. Complete the enrollment-session
                        backend before using this flow in production.
                    </p>

                @else

                    <div class="zo-notice">
                        <strong>Authenticated</strong>
                        <div>
                            You are signed in as
                            {{ auth()->user()->email }}.
                        </div>
                    </div>

                @endguest

            </section>

            {{-- STEP 4: CHECK ACCESS --}}
            @auth

                <section class="zo-card">

                    <h2 class="zo-card-title">
                        Verify Server Connection
                    </h2>

                    <p class="zo-description">
                        Before connecting, ZeitOps must verify the
                        server's SSH host identity. Once verified,
                        you can check SSH authentication.
                    </p>

                    <div class="zo-notice">
                        <strong>SSH host verification required</strong>
                        <div>
                            Compare the server's host fingerprint against
                            a trusted source, such as your VPS provider
                            console. A new SSH key alone does not verify
                            the identity of the remote server.
                        </div>
                    </div>

                    <div class="zo-actions">

                        <button
                            type="button"
                            id="check-access"
                            class="zo-btn zo-btn-primary"
                        >
                            Check Server Access
                        </button>

                    </div>

                    <div
                        id="check-result"
                        role="status"
                        aria-live="polite"
                    ></div>

                </section>

            @endauth

        @endisset

    </main>

    @isset($command)

        <script>
            document.addEventListener('DOMContentLoaded', function () {

                const copyButton = document.getElementById('copy-command');
                const commandElement = document.getElementById('zeitops-command');
                const copyFeedback = document.getElementById('copy-feedback');

                if (copyButton && commandElement) {

                    copyButton.addEventListener('click', async function () {

                        try {
                            await navigator.clipboard.writeText(
                                commandElement.textContent.trim()
                            );

                            copyButton.textContent = 'Copied!';
                            copyFeedback.textContent =
                                'Command copied to your clipboard.';

                            setTimeout(() => {
                                copyButton.textContent = 'Copy Command';
                            }, 2000);

                        } catch (error) {

                            copyFeedback.textContent =
                                'Unable to copy automatically. Please select and copy the command.';

                        }
                    });
                }

                @auth

                const checkButton = document.getElementById('check-access');
                const checkResult = document.getElementById('check-result');

                if (checkButton && checkResult) {

                    checkButton.addEventListener('click', async function () {

                        checkButton.disabled = true;
                        checkButton.textContent = 'Checking...';

                        checkResult.className =
                            'zo-result zo-result-loading';

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

                            const success = response.ok &&
                                (data.success === true || data.ssh?.ok === true);

                            if (success) {

                                checkResult.className =
                                    'zo-result zo-result-success';

                                checkResult.textContent =
                                    'Connection successful. ZeitOps can authenticate to your VPS.';

                            } else {

                                checkResult.className =
                                    'zo-result zo-result-error';

                                checkResult.textContent =
                                    data.message ||
                                    'Unable to verify the server connection.';

                            }

                        } catch (error) {

                            checkResult.className =
                                'zo-result zo-result-error';

                            checkResult.textContent =
                                'Unable to complete the connection check. Please try again.';

                        } finally {

                            checkButton.disabled = false;
                            checkButton.textContent = 'Check Server Access';

                        }
                    });
                }

                @endauth

            });
        </script>

    @endisset

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const form = document.getElementById('server-create-form');
            const button = document.getElementById('generate-command-btn');

            if (!form || !button) {
                return;
            }

            form.addEventListener('submit', function () {

                if (!form.checkValidity()) {
                    return;
                }

                button.disabled = true;
                button.textContent = 'Generating Command...';

            });

        });
    </script>

@endsection
