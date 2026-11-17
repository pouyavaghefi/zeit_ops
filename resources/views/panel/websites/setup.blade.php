
@extends('panel.layouts.master')

@section('wrapper')

    @php
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
            'react-express' => 'React + Express',
            'next-express' => 'Next.js + Express',
            'angular-express' => 'Angular + Express',
            'django' => 'Django',
            'flask' => 'Flask',
            'fastapi' => 'FastAPI',
            'express' => 'Express',
            'nestjs' => 'NestJS',
            'nextjs' => 'Next.js',
        ];

        $stackLabel = $stackLabels[$website->stack]
            ?? strtoupper($website->stack ?? 'Unknown');

        $frameworkLabel = $frameworkLabels[$website->framework]
            ?? ucfirst($website->framework ?? 'Unknown');
    @endphp

    <style>
        .zo-wizard {
            padding: 28px;
            background: #f5f7fc;
            min-height: calc(100vh - 75px);
            color: #273650;
        }

        .zo-wizard-inner {
            max-width: 1080px;
            margin: 0 auto;
        }

        .zo-wizard-back {
            display: inline-flex;
            gap: 8px;
            align-items: center;
            color: #6077a2;
            font-size: 13px;
            text-decoration: none;
            margin-bottom: 21px;
        }

        .zo-wizard-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 24px;
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
            padding: 9px 12px;
            border-radius: 25px;
            color: #ad771c;
            background: #fff1d6;
            font-size: 11px;
            font-weight: 700;
        }

        .zo-wizard-intro {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            padding: 22px;
            margin-bottom: 25px;
            border-radius: 14px;
            background: linear-gradient(110deg,#eaf2ff,#fff);
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

        .zo-wizard-card {
            background: white;
            border: 1px solid #e4eaf4;
            border-radius: 15px;
            margin-bottom: 18px;
            overflow: hidden;
        }

        .zo-wizard-card-head {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 19px 22px;
            border-bottom: 1px solid #edf0f6;
        }

        .zo-wizard-step {
            width: 36px;
            height: 36px;
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
            grid-template-columns: repeat(2,minmax(0,1fr));
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

        .zo-wizard-todo {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 16px;
            color: #61738d;
            font-size: 12px;
            line-height: 1.7;
        }

        .zo-wizard-todo:last-child {
            margin-bottom: 0;
        }

        .zo-wizard-todo i {
            color: #a2afc2;
            margin-top: 4px;
        }

        .zo-wizard-repo {
            word-break: break-word;
        }

        .zo-wizard-next {
            padding: 20px 23px;
            background: #f8faff;
            border-top: 1px solid #e8edf6;
            font-size: 12px;
            line-height: 1.8;
            color: #667892;
        }

        @media(max-width:650px) {
            .zo-wizard { padding: 16px; }
            .zo-wizard-header h1 { font-size: 23px; }
            .zo-wizard-detail-grid { grid-template-columns: 1fr; }
            .zo-wizard-card-body { padding: 17px; }
        }
    </style>

    <div class="zo-wizard">
        <div class="zo-wizard-inner">

            <a
                href="{{ route('panel.websites.all') }}"
                class="zo-wizard-back"
            >
                <i class="fas fa-arrow-left"></i>
                Back to All Websites
            </a>

            <div class="zo-wizard-header">
                <div>
                    <h1>{{ $website->domain }}</h1>
                    <p>
                        Website Setup ·
                        {{ $stackLabel }} ·
                        {{ $frameworkLabel }}
                    </p>
                </div>

                <span class="zo-wizard-pill">
                <i class="fas fa-clock me-1"></i>
                {{ ucwords(str_replace('_', ' ', $website->status)) }}
            </span>
            </div>

            <div class="zo-wizard-intro">
                <div class="zo-wizard-intro-icon">
                    <i class="fas fa-rocket"></i>
                </div>

                <div>
                    <h2>Let's get your website ready!</h2>
                    <p>
                        Your website configuration is saved in ZeitOps.
                        Review the connected server, application requirements,
                        and Git repository below. Actual VPS provisioning
                        and deployment have not started yet.
                    </p>
                </div>
            </div>

            {{-- STEP 1 --}}
            <section class="zo-wizard-card">
                <div class="zo-wizard-card-head">
                    <div class="zo-wizard-step done">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <h3>Website Registration</h3>
                        <p>Your website configuration is saved.</p>
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
                                {{ $website->runtime_version
                                    ?? $website->php_version
                                    ?? 'Not specified' }}
                            </strong>
                        </div>
                    </div>
                </div>
            </section>

            {{-- STEP 2 --}}
            <section class="zo-wizard-card">
                <div class="zo-wizard-card-head">
                    <div class="zo-wizard-step">2</div>
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
                            <strong>{{ $server['port'] ?? 22 }}</strong>
                        </div>

                        <div class="zo-wizard-detail">
                            <small>Last Recorded Connection Status</small>
                            <strong class="{{ $serverConnected
                            ? 'zo-wizard-good'
                            : 'zo-wizard-warning' }}">
                                <i class="fas {{ $serverConnected
                                ? 'fa-check-circle'
                                : 'fa-exclamation-circle' }}"></i>

                                {{ ucfirst($serverStatus) }}
                            </strong>
                        </div>
                    </div>

                    <p class="zo-wizard-description">
                        @if($serverConnected)
                            This server was previously marked connected.
                            A fresh SSH connection and system inspection
                            are still required before provisioning.
                        @else
                            The server does not currently have a recorded
                            connected status. Recheck its SSH connection
                            before attempting deployment.
                        @endif
                    </p>

                </div>
            </section>

            {{-- STEP 3 --}}
            <section class="zo-wizard-card">
                <div class="zo-wizard-card-head">
                    <div class="zo-wizard-step">3</div>
                    <div>
                        <h3>Server Requirements</h3>
                        <p>Inspect and prepare the selected stack.</p>
                    </div>
                </div>

                <div class="zo-wizard-card-body">
                    <div class="zo-wizard-todo">
                        <i class="far fa-circle"></i>
                        <span>
                        Inspect the operating system,
                        available resources and permissions.
                    </span>
                    </div>

                    <div class="zo-wizard-todo">
                        <i class="far fa-circle"></i>
                        <span>
                        Check the components required by
                        {{ $stackLabel }}.
                    </span>
                    </div>

                    <div class="zo-wizard-todo">
                        <i class="far fa-circle"></i>
                        <span>
                        Review the installation plan before
                        making any server changes.
                    </span>
                    </div>
                </div>
            </section>

            {{-- STEP 4 --}}
            <section class="zo-wizard-card">
                <div class="zo-wizard-card-head">
                    <div class="zo-wizard-step">4</div>
                    <div>
                        <h3>Git & Deployment</h3>
                        <p>Prepare the application for publishing.</p>
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
                        The next deployment stage will configure application
                        access, install dependencies, configure the web server,
                        and perform deployment health checks.
                    </p>
                </div>

                <div class="zo-wizard-next">
                    <i class="fas fa-info-circle me-1"></i>
                    Deployment is not available yet. This page shows
                    your saved configuration and the remaining work.
                    The next feature is the SSH preflight and
                    provisioning workflow.
                </div>
            </section>

        </div>
    </div>

@endsection
