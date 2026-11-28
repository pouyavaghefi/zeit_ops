@extends('panel.layouts.master')

@section('wrapper')

    @php
        /*
        |--------------------------------------------------------------------------
        | Website and Server Information
        |--------------------------------------------------------------------------
        */

        $serverStatus = strtolower(
            (string) ($server['status'] ?? 'unknown')
        );

        $serverConnected = $serverStatus === 'connected';

        $serverName = $server['name']
            ?? $server['server_name']
            ?? 'Registered VPS';

        $serverIp = $server['ip']
            ?? $server['ip_address']
            ?? 'Unavailable';

        $stackLabels = [
            'lamp' => 'LAMP',
            'lemp' => 'LEMP',
            'mern' => 'MERN',
            'mean' => 'MEAN',
            'python' => 'Python',
            'nodejs' => 'Node.js',
        ];

        $frameworkLabels = [
            'laravel' => 'Laravel',
            'symfony' => 'Symfony',
            'wordpress' => 'WordPress',
            'php' => 'Plain PHP',
            'react-express' => 'React + Express',
            'next-express' => 'Next.js + Express',
            'angular-express' => 'Angular + Express',
            'django' => 'Django',
            'flask' => 'Flask',
            'fastapi' => 'FastAPI',
            'express' => 'Express.js',
            'nestjs' => 'NestJS',
            'nextjs' => 'Next.js',
        ];

        $stackLabel = $stackLabels[$website->stack]
            ?? strtoupper($website->stack ?? 'Unknown');

        $frameworkLabel = $frameworkLabels[$website->framework]
            ?? ucfirst($website->framework ?? 'Unknown');

        $runtimeVersion = $website->runtime_version
            ?? $website->php_version
            ?? 'Not specified';

        $runtimeName = match ($website->stack) {
            'lamp', 'lemp' => 'PHP',
            'mern', 'mean', 'nodejs' => 'Node.js',
            'python' => 'Python',
            default => 'Runtime',
        };

        /*
        |--------------------------------------------------------------------------
        | Provisioning API
        |--------------------------------------------------------------------------
        |
        | These route names must be registered when the queue-based
        | provisioning backend is implemented.
        |
        | Route::has prevents this page from failing while those
        | endpoints are not yet available.
        |
        */

        $provisionStartUrl = \Illuminate\Support\Facades\Route::has(
            'panel.websites.provision.start'
        )
            ? route('panel.websites.provision.start', $website->id)
            : null;

        $provisionStatusUrl = \Illuminate\Support\Facades\Route::has(
            'panel.websites.provision.status'
        )
            ? route('panel.websites.provision.status', $website->id)
            : null;

        $provisionApiAvailable =
            $provisionStartUrl !== null &&
            $provisionStatusUrl !== null;

        $canProvision = $serverConnected && $provisionApiAvailable;

        $initialStatus = in_array(
            $website->status,
            ['provisioning', 'deploying'],
            true
        )
            ? 'running'
            : 'idle';

        /*
        |--------------------------------------------------------------------------
        | Static Display Checklist
        |--------------------------------------------------------------------------
        |
        | The backend supplies actual progress and task results.
        | These are only labels displayed before installation.
        |
        */

        $initialTasks = match ($website->stack) {
            'lamp' => [
                'Inspect operating system and permissions',
                'Update package information',
                'Install Apache',
                'Install PHP and required extensions',
                'Install MySQL/MariaDB and development tools',
                'Verify installed components',
            ],

            'lemp' => [
                'Inspect operating system and permissions',
                'Update package information',
                'Install Nginx',
                'Install PHP-FPM and required extensions',
                'Install MySQL/MariaDB and development tools',
                'Verify installed components',
            ],

            'mern', 'mean' => [
                'Inspect operating system and permissions',
                'Prepare Node.js runtime',
                'Prepare MongoDB database',
                'Prepare application runtime',
                'Verify installed components',
            ],

            'python' => [
                'Inspect operating system and permissions',
                'Prepare Python runtime',
                'Prepare virtual environment',
                'Prepare application server',
                'Verify installed components',
            ],

            'nodejs' => [
                'Inspect operating system and permissions',
                'Prepare Node.js runtime',
                'Prepare process management',
                'Prepare reverse proxy',
                'Verify installed components',
            ],

            default => [
                'Inspect server requirements',
                'Prepare technology stack',
                'Verify installed components',
            ],
        };
    @endphp

    <style>
        /* ==========================================
           ZEITOPS WEBSITE SETUP
        ========================================== */

        .zo-wizard {
            padding: 28px;
            min-height: calc(100vh - 75px);
            background: #f5f7fc;
            color: #273650;
        }

        .zo-wizard *,
        .zo-wizard *::before,
        .zo-wizard *::after {
            box-sizing: border-box;
        }

        .zo-wizard-inner {
            max-width: 1080px;
            margin: 0 auto;
        }

        .zo-wizard-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #667d9f;
            font-size: 13px;
            text-decoration: none;
            margin-bottom: 22px;
        }

        .zo-wizard-back:hover {
            color: #326df1;
        }

        .zo-wizard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        .zo-wizard-header h1 {
            font-size: 28px;
            font-weight: 750;
            color: #20314d;
            margin: 0 0 7px;
            overflow-wrap: anywhere;
        }

        .zo-wizard-header p {
            font-size: 13px;
            color: #8494ad;
            margin: 0;
        }

        .zo-wizard-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 13px;
            border-radius: 25px;
            color: #ad771c;
            background: #fff1d6;
            font-size: 11px;
            font-weight: 700;
        }

        .zo-wizard-pill.active {
            color: #148953;
            background: #e2f8ec;
        }

        .zo-wizard-pill.failed {
            color: #c44964;
            background: #ffebef;
        }

        /* Intro */

        .zo-wizard-intro {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            padding: 22px;
            margin-bottom: 25px;
            border-radius: 14px;
            background: linear-gradient(110deg, #eaf2ff, #fff);
            border: 1px solid #cfdefa;
        }

        .zo-wizard-intro-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #dce9ff;
            color: #346aee;
            width: 46px;
            height: 46px;
            border-radius: 12px;
            flex-shrink: 0;
            font-size: 20px;
        }

        .zo-wizard-intro h2 {
            font-size: 17px;
            font-weight: 750;
            margin: 0 0 8px;
            color: #25406c;
        }

        .zo-wizard-intro p {
            margin: 0;
            color: #617796;
            font-size: 12px;
            line-height: 1.8;
        }

        /* Step cards */

        .zo-wizard-card {
            background: #fff;
            border: 1px solid #e4eaf4;
            border-radius: 15px;
            margin-bottom: 18px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(25, 45, 90, .025);
        }

        .zo-wizard-card-head {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 19px 22px;
            border-bottom: 1px solid #edf0f6;
        }

        .zo-wizard-step {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e9efff;
            color: #3966df;
            font-size: 14px;
            font-weight: 750;
            border-radius: 10px;
            flex-shrink: 0;
        }

        .zo-wizard-step.done {
            background: #e4f8ee;
            color: #188956;
        }

        .zo-wizard-card-head h3 {
            font-size: 15px;
            font-weight: 700;
            margin: 0 0 4px;
            color: #253650;
        }

        .zo-wizard-card-head p {
            font-size: 11px;
            color: #8a99ad;
            margin: 0;
        }

        .zo-wizard-card-body {
            padding: 23px;
        }

        .zo-wizard-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 15px;
        }

        .zo-wizard-detail {
            background: #f7f9fd;
            border: 1px solid #edf0f6;
            border-radius: 9px;
            padding: 13px;
            min-width: 0;
        }

        .zo-wizard-detail small {
            display: block;
            color: #8c9aae;
            font-size: 11px;
            margin-bottom: 7px;
        }

        .zo-wizard-detail strong {
            display: block;
            font-size: 13px;
            color: #2f415d;
            overflow-wrap: anywhere;
        }

        .zo-wizard-good {
            color: #168757 !important;
        }

        .zo-wizard-warning {
            color: #b8801b !important;
        }

        .zo-wizard-description {
            font-size: 12px;
            line-height: 1.8;
            color: #71829b;
            margin: 16px 0 0;
        }

        /* ==========================================
           PROVISIONING UI
        ========================================== */

        #server-requirements {
            scroll-margin-top: 105px;
        }

        .zo-provision-intro {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 18px;
            background: #f1f6ff;
            border: 1px solid #dae7ff;
            border-radius: 12px;
            margin-bottom: 22px;
        }

        .zo-provision-intro-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 43px;
            height: 43px;
            flex-shrink: 0;
            background: #dfebff;
            color: #356aef;
            border-radius: 11px;
            font-size: 18px;
        }

        .zo-provision-intro h4 {
            font-size: 14px;
            font-weight: 750;
            color: #28436b;
            margin: 0 0 7px;
        }

        .zo-provision-intro p {
            font-size: 12px;
            color: #697f9d;
            line-height: 1.7;
            margin: 0;
        }

        .zo-provision-panel {
            border: 1px solid #e4eaf5;
            border-radius: 13px;
            background: #fff;
            padding: 21px;
        }

        .zo-provision-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 20px;
        }

        .zo-provision-current-label {
            display: block;
            font-size: 10px;
            font-weight: 650;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: #95a2b6;
            margin-bottom: 8px;
        }

        .zo-provision-current {
            display: flex;
            align-items: center;
            gap: 9px;
            color: #2d405e;
            font-size: 15px;
            font-weight: 750;
            line-height: 1.5;
        }

        .zo-provision-current i {
            color: #376aeb;
        }

        .zo-provision-status {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 11px;
            border-radius: 20px;
            background: #eef2f8;
            color: #7b8ba3;
            font-size: 11px;
            font-weight: 700;
        }

        .zo-provision-status.running {
            color: #346aec;
            background: #eaf1ff;
        }

        .zo-provision-status.completed {
            color: #138a56;
            background: #e5f8ed;
        }

        .zo-provision-status.failed {
            color: #c84660;
            background: #ffedf0;
        }

        .zo-provision-progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .zo-provision-progress-header span {
            color: #8a99ad;
            font-size: 11px;
        }

        .zo-provision-percentage {
            color: #3369eb !important;
            font-size: 20px !important;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
        }

        .zo-progress-track {
            width: 100%;
            height: 13px;
            background: #e9eef8;
            border-radius: 20px;
            overflow: hidden;
        }

        .zo-progress-fill {
            width: 0%;
            height: 100%;
            border-radius: 20px;
            background: linear-gradient(
                90deg,
                #327af8,
                #5142e9
            );
            transition: width .45s ease;
            position: relative;
        }

        .zo-progress-fill.running::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255,255,255,.35),
                transparent
            );
            animation: zo-progress-shine 1.8s linear infinite;
        }

        @keyframes zo-progress-shine {
            from { transform: translateX(-100%); }
            to { transform: translateX(100%); }
        }

        .zo-progress-description {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 11px;
            color: #8998ac;
            font-size: 11px;
        }

        /* Tasks */

        .zo-provision-tasks {
            margin-top: 23px;
            border-top: 1px solid #edf0f6;
            padding-top: 18px;
        }

        .zo-provision-tasks-title {
            font-size: 12px;
            color: #33445e;
            font-weight: 750;
            margin: 0 0 15px;
        }

        .zo-provision-task {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f0f3f8;
        }

        .zo-provision-task:last-child {
            border-bottom: 0;
        }

        .zo-task-icon {
            width: 31px;
            height: 31px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            color: #a6b2c4;
            background: #f0f3f8;
            font-size: 12px;
        }

        .zo-provision-task.done .zo-task-icon {
            background: #e5f8ed;
            color: #168957;
        }

        .zo-provision-task.running .zo-task-icon {
            background: #e6eeff;
            color: #3669ee;
        }

        .zo-provision-task.failed .zo-task-icon {
            background: #ffebef;
            color: #d34a65;
        }

        .zo-task-content {
            flex: 1;
            min-width: 0;
        }

        .zo-task-name {
            display: block;
            color: #354762;
            font-size: 12px;
            font-weight: 650;
            overflow-wrap: anywhere;
        }

        .zo-task-detail {
            display: block;
            font-size: 10px;
            color: #98a5b7;
            margin-top: 5px;
            overflow-wrap: anywhere;
        }

        .zo-task-state {
            font-size: 10px;
            font-weight: 700;
            color: #9aa8ba;
        }

        .zo-provision-task.done .zo-task-state {
            color: #198d59;
        }

        .zo-provision-task.running .zo-task-state {
            color: #396feb;
        }

        .zo-provision-task.failed .zo-task-state {
            color: #cf4661;
        }

        /* Actions */

        .zo-provision-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 23px;
            padding-top: 19px;
            border-top: 1px solid #edf0f6;
        }

        .zo-provision-actions p {
            font-size: 11px;
            color: #8997ab;
            margin: 0;
            flex: 1 1 220px;
            line-height: 1.7;
        }

        .zo-provision-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 12px 18px;
            border: 0;
            border-radius: 9px;
            background: linear-gradient(135deg,#2379ff,#4650df);
            color: #fff;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
            min-height: 44px;
        }

        .zo-provision-button:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        .zo-provision-button:hover:not(:disabled) {
            box-shadow: 0 5px 15px rgba(40, 104, 235, .2);
        }

        /* Messages */

        .zo-provision-message {
            display: none;
            padding: 14px 16px;
            margin-top: 17px;
            border-radius: 9px;
            font-size: 12px;
            line-height: 1.7;
        }

        .zo-provision-message.show {
            display: block;
        }

        .zo-provision-message.info {
            background: #edf4ff;
            color: #4068a4;
        }

        .zo-provision-message.success {
            background: #e7f8ef;
            color: #19764d;
        }

        .zo-provision-message.error {
            background: #ffedf0;
            color: #b73b55;
        }

        /* Installation log */

        .zo-provision-log {
            margin-top: 20px;
        }

        .zo-provision-log summary {
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
            color: #4b6693;
            padding: 9px 0;
        }

        .zo-provision-log pre {
            background: #172439;
            color: #c6d6ee;
            border-radius: 9px;
            padding: 17px;
            font-size: 11px;
            line-height: 1.8;
            margin: 10px 0 0;
            max-height: 250px;
            overflow: auto;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        /* Final section */

        .zo-wizard-repo {
            word-break: break-word;
        }

        .zo-wizard-next {
            padding: 20px 23px;
            background: #f8faff;
            border-top: 1px solid #e8edf6;
            color: #667892;
            font-size: 12px;
            line-height: 1.8;
        }

        @media(max-width: 650px) {
            .zo-wizard {
                padding: 16px;
            }

            .zo-wizard-header h1 {
                font-size: 23px;
            }

            .zo-wizard-detail-grid {
                grid-template-columns: 1fr;
            }

            .zo-wizard-card-body {
                padding: 17px;
            }

            .zo-provision-panel {
                padding: 15px;
            }

            .zo-provision-top {
                align-items: flex-start;
            }

            .zo-provision-actions {
                align-items: stretch;
            }

            .zo-provision-button {
                width: 100%;
            }
        }

        @media(prefers-reduced-motion: reduce) {
            .zo-progress-fill,
            .zo-provision-button {
                transition: none;
            }

            .zo-progress-fill.running::after {
                animation: none;
            }
        }
    </style>

    <div class="zo-wizard">
        <div class="zo-wizard-inner">

            {{-- BACK BUTTON --}}
            <a
                href="{{ route('panel.websites.all') }}"
                class="zo-wizard-back"
            >
                <i class="fas fa-arrow-left"></i>
                Back to All Websites
            </a>

            {{-- PAGE HEADER --}}
            <div class="zo-wizard-header">

                <div>
                    <h1>{{ $website->domain }}</h1>

                    <p>
                        Website Setup · {{ $stackLabel }} ·
                        {{ $frameworkLabel }}
                    </p>
                </div>

                <span
                    id="zo-website-status"
                    class="zo-wizard-pill {{ $website->status === 'active' ? 'active' : ($website->status === 'failed' ? 'failed' : '') }}"
                >
                <i class="fas fa-clock"></i>
                <span id="zo-website-status-text">
                    {{ ucwords(str_replace('_', ' ', $website->status)) }}
                </span>
            </span>

            </div>

            {{-- INTRODUCTION --}}
            <div class="zo-wizard-intro">

                <div class="zo-wizard-intro-icon">
                    <i class="fas fa-rocket"></i>
                </div>

                <div>
                    <h2>Let's get your website ready!</h2>

                    <p>
                        Your website has been registered in ZeitOps.
                        The next stage is preparing the server,
                        installing the required technologies,
                        and verifying the environment before deployment.
                    </p>
                </div>

            </div>

            {{-- ========================================
                 STEP 1: WEBSITE REGISTRATION
            ========================================= --}}
            <section class="zo-wizard-card">

                <div class="zo-wizard-card-head">

                    <div class="zo-wizard-step done">
                        <i class="fas fa-check"></i>
                    </div>

                    <div>
                        <h3>Website Registration</h3>
                        <p>Your website configuration has been saved.</p>
                    </div>

                </div>

                <div class="zo-wizard-card-body">

                    <div class="zo-wizard-detail-grid">

                        <div class="zo-wizard-detail">
                            <small>Website Name</small>
                            <strong>{{ $website->name }}</strong>
                        </div>

                        <div class="zo-wizard-detail">
                            <small>Domain</small>
                            <strong>{{ $website->domain }}</strong>
                        </div>

                        <div class="zo-wizard-detail">
                            <small>Technology Stack</small>
                            <strong>{{ $stackLabel }}</strong>
                        </div>

                        <div class="zo-wizard-detail">
                            <small>Framework / Runtime</small>
                            <strong>
                                {{ $frameworkLabel }} ·
                                {{ $runtimeName }} {{ $runtimeVersion }}
                            </strong>
                        </div>

                    </div>

                </div>

            </section>

            {{-- ========================================
                 STEP 2: SERVER CONNECTION
            ========================================= --}}
            <section class="zo-wizard-card">

                <div class="zo-wizard-card-head">

                    <div class="zo-wizard-step {{ $serverConnected ? 'done' : '' }}">
                        @if($serverConnected)
                            <i class="fas fa-check"></i>
                        @else
                            2
                        @endif
                    </div>

                    <div>
                        <h3>Server Connection</h3>
                        <p>Review your registered VPS connection.</p>
                    </div>

                </div>

                <div class="zo-wizard-card-body">

                    <div class="zo-wizard-detail-grid">

                        <div class="zo-wizard-detail">
                            <small>Server</small>
                            <strong>{{ $serverName }}</strong>
                        </div>

                        <div class="zo-wizard-detail">
                            <small>IP Address</small>
                            <strong>{{ $serverIp }}</strong>
                        </div>

                        <div class="zo-wizard-detail">
                            <small>SSH Port</small>
                            <strong>
                                {{ $server['port'] ?? 22 }}
                            </strong>
                        </div>

                        <div class="zo-wizard-detail">

                            <small>Last Recorded Connection Status</small>

                            <strong class="{{ $serverConnected ? 'zo-wizard-good' : 'zo-wizard-warning' }}">

                                <i class="fas {{ $serverConnected ? 'fa-check-circle' : 'fa-exclamation-circle' }}"></i>

                                {{ ucfirst($serverStatus) }}

                            </strong>

                        </div>

                    </div>

                    <p class="zo-wizard-description">
                        @if($serverConnected)
                            Your server was previously verified through SSH.
                            ZeitOps must establish a new authenticated
                            connection and inspect the server before
                            installing dependencies.
                        @else
                            The server does not have a recorded
                            connected status. Verify its SSH connection
                            before continuing.
                        @endif
                    </p>

                </div>

            </section>

            {{-- ========================================
                 STEP 3: SERVER REQUIREMENTS
            ========================================= --}}
            <section
                id="server-requirements"
                class="zo-wizard-card"
            >

                <div class="zo-wizard-card-head">

                    <div class="zo-wizard-step" id="zo-step-3-icon">
                        3
                    </div>

                    <div>
                        <h3>Server Requirements</h3>
                        <p>
                            Inspect and install the required
                            {{ $stackLabel }} components.
                        </p>
                    </div>

                </div>

                <div class="zo-wizard-card-body">

                    {{-- INFORMATION --}}
                    <div class="zo-provision-intro">

                        <div class="zo-provision-intro-icon">
                            <i class="fas fa-server"></i>
                        </div>

                        <div>
                            <h4>
                                Prepare {{ $stackLabel }} Environment
                            </h4>

                            <p>
                                ZeitOps will first inspect the registered VPS
                                and check the required dependencies.
                                After you confirm the installation,
                                the provisioning worker will configure
                                the approved components and report
                                real progress below.
                            </p>
                        </div>

                    </div>

                    {{-- PROVISIONING PANEL --}}
                    <div class="zo-provision-panel">

                        {{-- CURRENT OPERATION --}}
                        <div class="zo-provision-top">

                            <div>

                                <small class="zo-provision-current-label">
                                    Current Operation
                                </small>

                                <div
                                    id="zo-current-operation"
                                    class="zo-provision-current"
                                    aria-live="polite"
                                >
                                    <i class="fas fa-clock"></i>

                                    <span id="zo-current-operation-text">
                                    Waiting to start
                                </span>
                                </div>

                            </div>

                            <span
                                class="zo-provision-status"
                                id="zo-provision-status"
                            >
                            <i class="fas fa-circle"></i>
                            <span id="zo-provision-status-text">
                                Not Started
                            </span>
                        </span>

                        </div>

                        {{-- PROGRESS BAR --}}
                        <div class="zo-provision-progress-header">

                        <span>
                            Installation Progress
                        </span>

                            <strong
                                class="zo-provision-percentage"
                                id="zo-progress-percentage"
                            >
                                0%
                            </strong>

                        </div>

                        <div
                            class="zo-progress-track"
                            id="zo-progress-track"
                            role="progressbar"
                            aria-label="Server provisioning progress"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            aria-valuenow="0"
                        >
                            <div
                                id="zo-progress-fill"
                                class="zo-progress-fill"
                            ></div>
                        </div>

                        <div class="zo-progress-description">

                        <span id="zo-progress-details">
                            No installation is running.
                        </span>

                            <span id="zo-progress-count">
                            0 tasks completed
                        </span>

                        </div>

                        {{-- DEPENDENCY CHECKLIST --}}
                        <div class="zo-provision-tasks">

                            <h4 class="zo-provision-tasks-title">
                                <i class="fas fa-list-check me-2"></i>
                                Installation Checklist
                            </h4>

                            <div
                                id="zo-task-list"
                                aria-live="polite"
                            >
                                @foreach($initialTasks as $task)

                                    <div class="zo-provision-task">

                                        <div class="zo-task-icon">
                                            <i class="far fa-circle"></i>
                                        </div>

                                        <div class="zo-task-content">
                                        <span class="zo-task-name">
                                            {{ $task }}
                                        </span>

                                            <small class="zo-task-detail">
                                                Waiting to start
                                            </small>
                                        </div>

                                        <span class="zo-task-state">
                                        Pending
                                    </span>

                                    </div>

                                @endforeach
                            </div>

                        </div>

                        {{-- STATUS MESSAGE --}}
                        <div
                            id="zo-provision-message"
                            class="zo-provision-message"
                            role="status"
                            aria-live="polite"
                        ></div>

                        {{-- ACTIONS --}}
                        <div class="zo-provision-actions">

                            <p id="zo-provision-action-hint">
                                Installation will change the selected VPS.
                                Review the proposed changes and confirm
                                before starting.
                            </p>

                            <button
                                id="zo-start-provision"
                                type="button"
                                class="zo-provision-button"
                                disabled
                            >
                                <i class="fas fa-play"></i>

                                <span id="zo-start-button-text">
                                    Provisioning Not Available Yet
                                </span>
                            </button>

                        </div>

                        {{-- PROVISIONING LOG --}}
                        <details
                            id="zo-provision-log-container"
                            class="zo-provision-log"
                        >
                            <summary>
                                <i class="fas fa-terminal me-2"></i>
                                Installation Activity
                            </summary>

                            <pre id="zo-provision-log" aria-label="Installation activity">No activity recorded yet.</pre>
                        </details>

                    </div>

                    {{-- API / CONNECTION WARNINGS --}}
                    @unless($serverConnected)

                        <div class="zo-provision-message show error">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            The selected VPS is not marked as connected.
                            Recheck the server connection before provisioning.
                        </div>

                    @endunless

                    @unless($provisionApiAvailable)

                        <div class="zo-provision-message show info">
                            <i class="fas fa-info-circle me-2"></i>
                            The provisioning service has not been configured.
                            Install the Laravel provisioning routes and
                            background worker to enable server setup.
                        </div>

                    @endunless

                </div>

            </section>

            {{-- ========================================
                 STEP 4: GIT & DEPLOYMENT
            ========================================= --}}
            <section
                id="git-deployment"
                class="zo-wizard-card"
            >

                <div class="zo-wizard-card-head">

                    <div class="zo-wizard-step">4</div>

                    <div>
                        <h3>Git & Deployment</h3>
                        <p>
                            Deploy your application after
                            the server environment is ready.
                        </p>
                    </div>

                </div>

                <div class="zo-wizard-card-body">

                    <div class="zo-wizard-detail-grid">

                        <div class="zo-wizard-detail">

                            <small>Repository</small>

                            <strong class="zo-wizard-repo">
                                {{ $website->repository_url ?: 'Not configured' }}
                            </strong>

                        </div>

                        <div class="zo-wizard-detail">

                            <small>Branch</small>

                            <strong>
                                {{ $website->repository_branch ?: 'main' }}
                            </strong>

                        </div>

                    </div>

                    <p class="zo-wizard-description">
                        Once the server dependencies are installed and
                        verified, ZeitOps can proceed with repository
                        access, application configuration, web-server
                        configuration, and health checks.
                    </p>

                </div>

                <div
                    class="zo-wizard-next"
                    id="zo-deployment-notice"
                >
                    @if($website->status === 'active')
                        <i class="fas fa-check-circle me-2"></i>
                        HTTPS deployment active. Last deployed: {{ optional($website->last_deployed_at)->format('d M Y H:i') ?? 'Recently' }}.
                    @elseif($website->status === 'deployed')
                        <i class="fas fa-globe me-2"></i>
                        Release installed and verified locally. Check public DNS and HTTPS before going live.
                    @elseif($website->status === 'deploying')
                        <i class="fas fa-spinner fa-spin me-2"></i>
                        Deployment is running. Refresh this page for the latest result.
                    @elseif($website->status === 'failed')
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Deployment failed. Inspect ZeitOps provisioning log and VPS site deployment log.
                    @else
                        <i class="fas fa-code-branch me-2"></i>
                        Deployment starts after server provisioning. Existing completed servers can dispatch DeployWebsiteJob separately.
                    @endif
                </div>

            </section>

        </div>
    </div>

    {{-- Release deployment controls: independent from server provisioning. --}}
    @if($website->stack === 'lamp' && $website->framework === 'laravel')
        <section id="zo-deploy-panel" style="max-width:1080px;margin:20px auto;padding:24px;border:1px solid #dfe6f3;border-radius:14px;background:white;color:#24354c">
            <h2 style="font-size:21px;margin:0 0 8px">Website deployment</h2>
            <p><a href="{{ route('panel.websites.domains.index', $website->id) }}" style="font-weight:700;color:#2563eb">🔒 Manage SSL &amp; Domains</a></p>
            <p style="margin-bottom:18px">Deploy the configured GitHub branch to this server. Each attempt is recorded separately.</p>
            <label style="display:flex;gap:10px;align-items:flex-start;margin-bottom:16px">
                <input id="zo-deploy-migrate" type="checkbox" style="margin-top:5px">
                <span><strong>Run Laravel database migrations</strong><small style="display:block;color:#697c97">Select for first-time Laravel setup when required tables are missing. Review migrations and back up existing data before selecting for production updates.</small></span>
            </label>
            <button id="zo-deploy-start" type="button" class="zo-provision-button" style="display:inline-block" @if(!$serverConnected) disabled @endif>Deploy website</button>
            <p id="zo-deploy-state" role="status" aria-live="polite" style="margin:14px 0;color:#516782">Checking deployment history…</p>
            <div id="zo-deploy-error" role="alert" style="color:#ab253c;margin:8px 0"></div>
            <h3 style="font-size:15px;margin-top:18px">Recent deployments</h3>
            <div id="zo-deploy-history" style="font-size:13px;color:#516782">No deployment history yet.</div>
        </section>
        <script>
            (() => {
                const start = document.getElementById('zo-deploy-start');
                const state = document.getElementById('zo-deploy-state');
                const history = document.getElementById('zo-deploy-history');
                const error = document.getElementById('zo-deploy-error');
                const migrate = document.getElementById('zo-deploy-migrate');
                const statusUrl = @json(route('panel.websites.deploy.status', $website->id));
                const startUrl = @json(route('panel.websites.deploy.start', $website->id));
                let busy = false;
                function row(run) {
                    const line = document.createElement('div');
                    line.style.cssText = 'padding:9px 0;border-bottom:1px solid #e8edf5';
                    const text = `#${run.id} · ${run.status} · ${run.stage}${run.commit ? ' · '+run.commit.slice(0,8) : ''}${run.run_migrations ? ' · migrations' : ''}`;
                    line.textContent = text;
                    if (run.error) {
                        const detail = document.createElement('div');
                        detail.style.color = '#ab253c';
                        detail.textContent = run.error;
                        line.appendChild(detail);
                    }
                    return line;
                }
                async function refresh() {
                    try {
                        const res = await fetch(statusUrl, {credentials: 'same-origin', headers:{Accept:'application/json'}});
                        if (!res.ok) throw new Error('Unable to fetch deployment status.');
                        const data = await res.json();
                        const latest = data.latest;
                        busy = !!latest && ['queued','running'].includes(latest.status);
                        start.disabled = busy || !@json($serverConnected);
                        state.textContent = latest ? `Latest: ${latest.status} (${latest.stage})` : 'No deployment attempts yet.';
                        history.replaceChildren();
                        (data.history || []).forEach(item => history.appendChild(row(item)));
                    } catch (e) { error.textContent = e.message; }
                }
                start.addEventListener('click', async () => {
                    if (busy) return;
                    if (migrate.checked && !confirm('Database migrations can change existing data. Have you reviewed the migrations and backed up your database?')) return;
                    start.disabled = true;
                    error.textContent = '';
                    try {
                        const res = await fetch(startUrl, {
                            method:'POST', credentials:'same-origin',
                            headers:{'Content-Type':'application/json',Accept:'application/json','X-CSRF-TOKEN':@json(csrf_token())},
                            body: JSON.stringify({run_migrations:migrate.checked}),
                        });
                        const payload = await res.json();
                        if (!res.ok) throw new Error(payload.message || 'Unable to queue deployment.');
                        state.textContent = 'Deployment queued.';
                        await refresh();
                    } catch (e) { error.textContent = e.message; start.disabled = false; }
                });
                refresh();
                setInterval(refresh, 6000);
            })();
        </script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            'use strict';

            /*
            |--------------------------------------------------------------------------
            | Configuration
            |--------------------------------------------------------------------------
            */

            const config = {
                websiteId: @json($website->id),
                websiteStatus: @json($website->status),
                serverConnected: @json($serverConnected),
                apiAvailable: @json($provisionApiAvailable),

                startUrl: @json($provisionStartUrl),
                statusUrl: @json($provisionStatusUrl),

                csrf: @json(csrf_token()),

                initialStatus: @json($initialStatus),

                scrollOnLoad:
                    window.location.hash === '#server-requirements'
            };

            /*
            |--------------------------------------------------------------------------
            | Elements
            |--------------------------------------------------------------------------
            */

            const elements = {
                section:
                    document.getElementById('server-requirements'),

                startButton:
                    document.getElementById('zo-start-provision'),

                startButtonText:
                    document.getElementById('zo-start-button-text'),

                currentOperation:
                    document.getElementById('zo-current-operation'),

                currentOperationText:
                    document.getElementById('zo-current-operation-text'),

                status:
                    document.getElementById('zo-provision-status'),

                statusText:
                    document.getElementById('zo-provision-status-text'),

                percentage:
                    document.getElementById('zo-progress-percentage'),

                progressBar:
                    document.getElementById('zo-progress-track'),

                progressFill:
                    document.getElementById('zo-progress-fill'),

                progressDetails:
                    document.getElementById('zo-progress-details'),

                progressCount:
                    document.getElementById('zo-progress-count'),

                taskList:
                    document.getElementById('zo-task-list'),

                message:
                    document.getElementById('zo-provision-message'),

                log:
                    document.getElementById('zo-provision-log'),

                actionHint:
                    document.getElementById('zo-provision-action-hint'),

                websiteStatus:
                    document.getElementById('zo-website-status'),

                websiteStatusText:
                    document.getElementById('zo-website-status-text'),

                stepIcon:
                    document.getElementById('zo-step-3-icon'),

                deploymentNotice:
                    document.getElementById('zo-deployment-notice'),
            };

            /*
            |--------------------------------------------------------------------------
            | State
            |--------------------------------------------------------------------------
            */

            let pollingTimer = null;
            let pollingActive = false;
            let starting = false;
            let consecutiveErrors = 0;
            let currentState = config.initialStatus;
            let lastProgress = 0;

            /*
            |--------------------------------------------------------------------------
            | Helpers
            |--------------------------------------------------------------------------
            */

            function escapeText(value) {
                return String(value ?? '');
            }

            function clampPercentage(value) {
                const parsed = Number(value);

                if (!Number.isFinite(parsed)) {
                    return 0;
                }

                return Math.max(
                    0,
                    Math.min(100, Math.round(parsed))
                );
            }

            function setMessage(message, type = 'info') {
                if (!message) {
                    elements.message.textContent = '';
                    elements.message.className =
                        'zo-provision-message';
                    return;
                }

                elements.message.textContent = message;

                elements.message.className =
                    'zo-provision-message show ' + type;
            }

            function setProgress(percent) {

                const value = clampPercentage(percent);

                elements.percentage.textContent = value + '%';

                elements.progressFill.style.width = value + '%';

                elements.progressBar.setAttribute(
                    'aria-valuenow',
                    String(value)
                );

                lastProgress = value;
            }

            function setStatus(status) {

                const allowed = [
                    'idle',
                    'queued',
                    'running',
                    'completed',
                    'failed'
                ];

                const normalized = allowed.includes(status)
                    ? status
                    : 'idle';

                currentState = normalized;

                const labels = {
                    idle: 'Not Started',
                    queued: 'Queued',
                    running: 'Installing',
                    completed: 'Completed',
                    failed: 'Failed',
                };

                elements.status.className =
                    'zo-provision-status ' + normalized;

                elements.statusText.textContent =
                    labels[normalized];

                elements.progressFill.classList.toggle(
                    'running',
                    normalized === 'running'
                );

                if (normalized === 'completed') {

                    elements.startButton.disabled = true;

                    elements.startButtonText.textContent =
                        'Installation Complete';

                    elements.stepIcon.classList.add('done');
                    elements.stepIcon.textContent = '';

                    const icon = document.createElement('i');
                    icon.className = 'fas fa-check';

                    elements.stepIcon.appendChild(icon);

                    elements.deploymentNotice.textContent =
                        'Server dependencies are ready. ' +
                        'The next stage is configuring and deploying ' +
                        'the application.';

                } else if (
                    normalized === 'running' ||
                    normalized === 'queued'
                ) {

                    elements.startButton.disabled = true;

                    elements.startButtonText.textContent =
                        normalized === 'queued'
                            ? 'Waiting for Worker...'
                            : 'Installation Running...';

                } else {

                    elements.startButton.disabled =
                        !config.serverConnected ||
                        !config.apiAvailable ||
                        starting;

                    elements.startButtonText.textContent =
                        normalized === 'failed'
                            ? 'Review & Retry'
                            : 'Check & Prepare Server';
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Render Task Checklist
            |--------------------------------------------------------------------------
            |
            | Expected task object:
            |
            | {
            |   id: "php",
            |   name: "Install PHP 8.3",
            |   status: "completed",
            |   detail: "PHP and PDO extensions verified"
            | }
            |
            */

            function renderTasks(tasks) {

                if (!Array.isArray(tasks)) return;

                elements.taskList.replaceChildren();

                const icons = {
                    pending: 'far fa-circle',
                    queued: 'far fa-clock',
                    running: 'fas fa-spinner fa-spin',
                    completed: 'fas fa-check',
                    failed: 'fas fa-times',
                    skipped: 'fas fa-minus',
                };

                const labels = {
                    pending: 'Pending',
                    queued: 'Queued',
                    running: 'Running',
                    completed: 'Completed',
                    failed: 'Failed',
                    skipped: 'Skipped',
                };

                tasks.forEach(function (task) {

                    const status = Object.prototype.hasOwnProperty.call(
                        labels,
                        task.status
                    ) ? task.status : 'pending';

                    const row = document.createElement('div');

                    row.className =
                        'zo-provision-task ' +
                        (status === 'completed'
                            ? 'done'
                            : status);

                    const iconWrapper =
                        document.createElement('div');

                    iconWrapper.className = 'zo-task-icon';

                    const icon =
                        document.createElement('i');

                    icon.className = icons[status];

                    iconWrapper.appendChild(icon);

                    const content =
                        document.createElement('div');

                    content.className = 'zo-task-content';

                    const name =
                        document.createElement('span');

                    name.className = 'zo-task-name';
                    name.textContent =
                        escapeText(task.name || 'Operation');

                    const detail =
                        document.createElement('small');

                    detail.className = 'zo-task-detail';
                    detail.textContent =
                        escapeText(task.detail || '');

                    content.append(name, detail);

                    const state =
                        document.createElement('span');

                    state.className = 'zo-task-state';
                    state.textContent = labels[status];

                    row.append(iconWrapper, content, state);

                    elements.taskList.appendChild(row);
                });
            }

            /*
            |--------------------------------------------------------------------------
            | Update From Backend
            |--------------------------------------------------------------------------
            |
            | Expected status response:
            |
            | {
            |   "status": "running",
            |   "percentage": 50,
            |   "current_operation": "Installing PHP 8.3",
            |   "completed_tasks": 3,
            |   "total_tasks": 6,
            |   "message": "Installing PHP extensions",
            |   "tasks": [...],
            |   "logs": [...]
            | }
            |
            */

            function applyProvisioningData(data) {

                if (!data || typeof data !== 'object') {
                    return;
                }

                const status = data.status || 'idle';

                setStatus(status);

                if (data.percentage !== undefined) {
                    setProgress(data.percentage);
                }

                const operation =
                    data.current_operation ||
                    (
                        status === 'completed'
                            ? 'All dependencies installed'
                            : status === 'failed'
                                ? 'Installation failed'
                                : status === 'queued'
                                    ? 'Waiting for provisioning worker'
                                    : 'Waiting to start'
                    );

                elements.currentOperationText.textContent =
                    operation;

                const currentIcon =
                    elements.currentOperation.querySelector('i');

                if (currentIcon) {
                    currentIcon.className =
                        status === 'running'
                            ? 'fas fa-spinner fa-spin'
                            : status === 'completed'
                                ? 'fas fa-check-circle'
                                : status === 'failed'
                                    ? 'fas fa-exclamation-circle'
                                    : 'fas fa-clock';
                }

                const completed = Number(data.completed_tasks);
                const total = Number(data.total_tasks);

                if (
                    Number.isFinite(completed) &&
                    Number.isFinite(total) &&
                    total > 0
                ) {
                    elements.progressCount.textContent =
                        completed + ' of ' + total +
                        ' tasks completed';
                } else {
                    elements.progressCount.textContent =
                        'Progress: ' + lastProgress + '%';
                }

                elements.progressDetails.textContent =
                    data.message ||
                    (
                        status === 'completed'
                            ? 'Server dependencies have been verified.'
                            : status === 'running'
                                ? 'Installation in progress...'
                                : 'No installation is running.'
                    );

                if (Array.isArray(data.tasks)) {
                    renderTasks(data.tasks);
                }

                if (Array.isArray(data.logs)) {
                    elements.log.textContent =
                        data.logs
                            .map(function (line) {
                                return escapeText(line);
                            })
                            .join('\n') ||
                        'No activity recorded yet.';
                }

                if (status === 'completed') {

                    setMessage(
                        'Server dependencies have been installed ' +
                        'and verified successfully. You can now ' +
                        'continue to application deployment.',
                        'success'
                    );

                    stopPolling();

                } else if (status === 'failed') {

                    setMessage(
                        data.error ||
                        data.message ||
                        'Server provisioning failed. Review the ' +
                        'installation activity before retrying.',
                        'error'
                    );

                    stopPolling();

                } else if (status === 'running') {

                    setMessage(
                        'Installation is running on the VPS. ' +
                        'You can leave this page and return later ' +
                        'to check progress.',
                        'info'
                    );

                } else if (status === 'queued') {

                    setMessage(
                        'The installation job has been queued. ' +
                        'Waiting for an available worker.',
                        'info'
                    );

                } else {

                    setMessage('', 'info');
                }

            }

            /*
            |--------------------------------------------------------------------------
            | Request Helper
            |--------------------------------------------------------------------------
            */


            async function requestJson(url, options = {}) {

                const response = await fetch(url, {
                    ...options,

                    credentials: 'same-origin',
                    cache: 'no-store',
                    redirect: 'follow',

                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...(options.headers || {})
                    }
                });

                const contentType =
                    response.headers.get('content-type') || '';

                if (!contentType.includes('application/json')) {

                    console.error('Unexpected provisioning response', {
                        url: url,
                        status: response.status,
                        redirected: response.redirected,
                        finalUrl: response.url,
                        contentType: contentType
                    });

                    if (response.status === 419) {
                        throw new Error(
                            'Your session has expired. Refresh the page and try again.'
                        );
                    }

                    if (
                        response.redirected ||
                        response.status === 401
                    ) {
                        throw new Error(
                            'Your request was redirected or your session expired. ' +
                            'Check your login and membership status.'
                        );
                    }

                    throw new Error(
                        'Provisioning API returned HTTP ' +
                        response.status +
                        ' instead of JSON. Check Laravel logs and the Network response.'
                    );
                }

                const result = await response.json();

                if (!response.ok) {

                    console.error('Provisioning API error', result);

                    throw new Error(
                        result.message ||
                        'Provisioning request failed (HTTP ' +
                        response.status + ').'
                    );
                }

                return result;
            }

            /*
            |--------------------------------------------------------------------------
            | Poll Actual Provisioning Progress
            |--------------------------------------------------------------------------
            */

            async function loadStatus() {

                if (!config.statusUrl) return;

                const data = await requestJson(config.statusUrl);

                consecutiveErrors = 0;

                applyProvisioningData(data);
            }

            async function pollStatus() {

                if (!pollingActive) return;

                try {

                    await loadStatus();

                } catch (error) {

                    consecutiveErrors++;

                    if (consecutiveErrors >= 5) {
                        setMessage(
                            'Unable to retrieve installation progress. ' +
                            'The server job may still be running. ' +
                            'Refresh this page to reconnect.',
                            'error'
                        );

                        stopPolling();
                        return;
                    }

                }

                if (pollingActive) {
                    pollingTimer = window.setTimeout(
                        pollStatus,
                        1500
                    );
                }
            }

            function startPolling() {

                if (pollingActive) return;

                pollingActive = true;
                consecutiveErrors = 0;

                pollStatus();
            }

            function stopPolling() {

                pollingActive = false;

                if (pollingTimer !== null) {
                    window.clearTimeout(pollingTimer);
                    pollingTimer = null;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Start Provisioning
            |--------------------------------------------------------------------------
            */

            async function startProvisioning() {

                if (
                    !config.serverConnected ||
                    !config.apiAvailable ||
                    starting ||
                    currentState === 'running' ||
                    currentState === 'queued' ||
                    currentState === 'completed'
                ) {
                    return;
                }

                /*
                 * This browser confirmation is UX only.
                 * The backend MUST also validate user ownership,
                 * active membership, privileges, stack support,
                 * and installation approval.
                 */

                const approved = window.confirm(
                    'Start server preparation for ' +
                    @json($website->domain) + '?\n\n' +
                    'This operation may install software packages ' +
                    'and change services on the selected VPS.\n\n' +
                    'Continue only if you authorize these changes.'
                );

                if (!approved) return;

                starting = true;
                elements.startButton.disabled = true;
                elements.startButtonText.textContent =
                    'Submitting Installation...';

                setMessage(
                    'Submitting the installation request...',
                    'info'
                );

                try {

                    const result = await requestJson(
                        config.startUrl,
                        {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': config.csrf,
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                confirmed: true
                            })
                        }
                    );

                    /*
                     * Backend may return the current job state
                     * or simply confirm that a job was queued.
                     */

                    if (
                        result &&
                        typeof result.status === 'string'
                    ) {
                        applyProvisioningData(result);
                    } else {
                        setStatus('queued');
                        elements.currentOperationText.textContent =
                            'Waiting for provisioning worker';
                    }

                    startPolling();

                } catch (error) {

                    setMessage(error.message, 'error');

                    setStatus('idle');

                } finally {

                    starting = false;

                    if (
                        currentState === 'idle' ||
                        currentState === 'failed'
                    ) {
                        elements.startButton.disabled = false;
                    }

                }
            }

            /*
            |--------------------------------------------------------------------------
            | Events
            |--------------------------------------------------------------------------
            */

            elements.startButton.addEventListener(
                'click',
                startProvisioning
            );

            /*
            |--------------------------------------------------------------------------
            | Initialization
            |--------------------------------------------------------------------------
            */

            setProgress(0);
            setStatus(config.initialStatus);

            if (
                config.apiAvailable &&
                config.serverConnected
            ) {

                /*
                 * On every visit, read the current status.
                 * This restores progress after a refresh.
                 */

                loadStatus()
                    .then(function () {
                        if (
                            currentState === 'running' ||
                            currentState === 'queued'
                        ) {
                            startPolling();
                        }
                    })
                    .catch(function () {
                        setMessage(
                            'The provisioning status is currently unavailable.',
                            'error'
                        );
                    });

            }

            /*
             * Scroll only when requested through:
             * /panel/websites/{id}/setup#server-requirements
             */

            if (config.scrollOnLoad && elements.section) {

                window.requestAnimationFrame(function () {

                    elements.section.scrollIntoView({
                        behavior: window.matchMedia(
                            '(prefers-reduced-motion: reduce)'
                        ).matches ? 'auto' : 'smooth',
                        block: 'start'
                    });

                });

            }

            window.addEventListener('pagehide', stopPolling);

        });
    </script>

@endsection
