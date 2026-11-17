
@extends('panel.layouts.master')

@section('wrapper')

    @php
        $totalWebsites = $websites->count();
        $activeWebsites = $websites->where('status', 'active')->count();
        $pendingWebsites = $websites->whereIn('status', [
            'pending_provisioning',
            'provisioning',
            'deploying'
        ])->count();
        $failedWebsites = $websites->where('status', 'failed')->count();

        $statusLabels = [
            'pending_provisioning' => 'Pending Provisioning',
            'provisioning' => 'Provisioning',
            'deploying' => 'Deploying',
            'active' => 'Active',
            'failed' => 'Failed',
            'suspended' => 'Suspended',
        ];

        $statusIcons = [
            'pending_provisioning' => 'fa-clock',
            'provisioning' => 'fa-cogs',
            'deploying' => 'fa-cloud-upload-alt',
            'active' => 'fa-check-circle',
            'failed' => 'fa-exclamation-circle',
            'suspended' => 'fa-pause-circle',
        ];

        $stackNames = [
            'lamp' => 'LAMP',
            'lemp' => 'LEMP',
            'mern' => 'MERN',
            'mean' => 'MEAN',
            'python' => 'Python',
            'nodejs' => 'Node.js',
        ];

        $frameworkNames = [
            'laravel' => 'Laravel',
            'symfony' => 'Symfony',
            'wordpress' => 'WordPress',
            'php' => 'PHP',
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
    @endphp

    <style>
        /* ==========================================
           ZEITOPS — ALL WEBSITES
        ========================================== */

        .zo-websites {
            padding: 28px;
            min-height: calc(100vh - 75px);
            background: #f5f7fc;
            color: #273650;
        }

        .zo-websites *,
        .zo-websites *::before,
        .zo-websites *::after {
            box-sizing: border-box;
        }

        .zo-websites-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 18px;
            margin-bottom: 26px;
        }

        .zo-breadcrumb {
            font-size: 12px;
            color: #8b99ae;
            margin-bottom: 9px;
        }

        .zo-websites-header h1 {
            color: #1a2940;
            font-size: 28px;
            font-weight: 750;
            margin: 0 0 7px;
        }

        .zo-websites-header p {
            color: #8794a9;
            font-size: 13px;
            margin: 0;
        }

        .zo-add-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 12px 19px;
            background: linear-gradient(135deg, #1770ff, #4554e8);
            border: none;
            border-radius: 10px;
            color: #fff !important;
            font-size: 13px;
            font-weight: 650;
            text-decoration: none;
            white-space: nowrap;
            box-shadow: 0 5px 14px rgba(42, 98, 225, .15);
            transition: transform .2s, box-shadow .2s;
        }

        .zo-add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(42, 98, 225, .23);
        }

        /* Statistics */

        .zo-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 25px;
        }

        .zo-stat-card {
            background: white;
            border: 1px solid #e7ecf5;
            border-radius: 14px;
            padding: 19px;
            display: flex;
            align-items: center;
            gap: 15px;
            min-width: 0;
            box-shadow: 0 4px 18px rgba(25, 44, 90, .035);
        }

        .zo-stat-icon {
            width: 47px;
            height: 47px;
            min-width: 47px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 19px;
        }

        .zo-stat-icon.blue {
            background: #e9f0ff;
            color: #3168ea;
        }

        .zo-stat-icon.green {
            background: #e6f8ef;
            color: #15955c;
        }

        .zo-stat-icon.orange {
            background: #fff3df;
            color: #c98919;
        }

        .zo-stat-icon.red {
            background: #ffeaed;
            color: #dd4a67;
        }

        .zo-stat-label {
            display: block;
            font-size: 11px;
            color: #8896aa;
            margin-bottom: 5px;
        }

        .zo-stat-value {
            font-size: 25px;
            font-weight: 750;
            line-height: 1.1;
            color: #22334d;
        }

        /* Main panel */

        .zo-list-panel {
            background: #fff;
            border: 1px solid #e7ecf5;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(25, 44, 90, .035);
        }

        .zo-list-header {
            padding: 21px 23px;
            border-bottom: 1px solid #edf0f6;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }

        .zo-list-header h2 {
            font-size: 17px;
            font-weight: 700;
            color: #23334e;
            margin: 0 0 5px;
        }

        .zo-list-header p {
            color: #909db0;
            font-size: 12px;
            margin: 0;
        }

        .zo-search {
            position: relative;
            width: min(100%, 290px);
        }

        .zo-search i {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca9ba;
            font-size: 13px;
            pointer-events: none;
        }

        .zo-search input {
            width: 100%;
            height: 41px;
            padding: 10px 13px 10px 37px;
            background: #f8faff;
            border: 1px solid #e2e8f3;
            outline: none;
            border-radius: 9px;
            font-size: 12px;
            color: #263750;
        }

        .zo-search input:focus {
            border-color: #5b8af4;
            box-shadow: 0 0 0 3px rgba(65, 115, 235, .09);
        }

        /* Website cards */

        .zo-website-list {
            padding: 17px;
            display: grid;
            gap: 14px;
        }

        .zo-website-card {
            border: 1px solid #e6ebf5;
            border-radius: 13px;
            padding: 19px 20px;
            background: #fff;
            min-width: 0;
            transition: border-color .2s, box-shadow .2s;
        }

        .zo-website-card:hover {
            border-color: #cbdaf9;
            box-shadow: 0 5px 18px rgba(39, 88, 192, .07);
        }

        .zo-website-primary {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
        }

        .zo-website-identity {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
            flex: 1 1 240px;
        }

        .zo-website-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 49px;
            height: 49px;
            background: linear-gradient(135deg, #e8f0ff, #efedff);
            color: #3765e9;
            border-radius: 12px;
            font-size: 20px;
        }

        .zo-website-name {
            font-size: 16px;
            font-weight: 700;
            color: #24334c;
            margin: 0 0 5px;
            overflow-wrap: anywhere;
        }

        .zo-website-domain {
            font-size: 12px;
            color: #8090a7;
            overflow-wrap: anywhere;
        }

        .zo-website-domain i {
            margin-right: 5px;
        }

        /* Status badges */

        .zo-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            flex-shrink: 0;
            max-width: 100%;
            padding: 8px 12px;
            border-radius: 24px;
            font-size: 11px;
            font-weight: 700;
            white-space: normal;
            text-align: center;
            line-height: 1.4;
        }

        .zo-status.pending_provisioning,
        .zo-status.provisioning,
        .zo-status.deploying {
            background: #fff3dd;
            color: #b47a17;
        }

        .zo-status.active {
            background: #e5f8ed;
            color: #188955;
        }

        .zo-status.failed {
            background: #ffedf0;
            color: #ca4160;
        }

        .zo-status.suspended {
            background: #eff1f5;
            color: #68768d;
        }

        /* Details */

        .zo-website-meta {
            display: flex;
            align-items: stretch;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 18px;
        }

        .zo-meta-item {
            min-width: 110px;
            flex: 1 1 125px;
            padding: 11px 13px;
            background: #f8faff;
            border: 1px solid #f0f2f8;
            border-radius: 9px;
        }

        .zo-meta-item small {
            display: block;
            font-size: 10px;
            color: #96a2b4;
            margin-bottom: 7px;
        }

        .zo-meta-item strong {
            display: block;
            color: #354762;
            font-size: 12px;
            font-weight: 650;
            overflow-wrap: anywhere;
        }

        /* Bottom row */

        .zo-website-bottom {
            margin-top: 17px;
            padding-top: 15px;
            border-top: 1px solid #edf0f6;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
        }

        .zo-repository {
            display: flex;
            align-items: center;
            gap: 9px;
            color: #7e8da3;
            font-size: 11px;
            min-width: 0;
            flex: 1 1 200px;
        }

        .zo-repository i {
            color: #5467a0;
            font-size: 15px;
            flex-shrink: 0;
        }

        .zo-repository span {
            overflow-wrap: anywhere;
            min-width: 0;
        }

        .zo-website-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 9px;
        }

        .zo-action-secondary {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 12px;
            border-radius: 8px;
            background: #eff3ff;
            color: #4165d8;
            font-size: 11px;
            font-weight: 650;
            text-decoration: none;
            transition: background .2s;
        }

        .zo-action-secondary:hover {
            background: #e1eaff;
            color: #2d50c4;
        }

        /* Empty states */

        .zo-empty {
            padding: 55px 22px;
            text-align: center;
        }

        .zo-empty-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 70px;
            height: 70px;
            background: #edf3ff;
            color: #416ee9;
            border-radius: 18px;
            font-size: 28px;
            margin-bottom: 18px;
        }

        .zo-empty h3 {
            font-size: 17px;
            font-weight: 700;
            color: #31415b;
            margin-bottom: 9px;
        }

        .zo-empty p {
            color: #8a98ab;
            font-size: 12px;
            margin-bottom: 20px;
        }

        .zo-feedback {
            border-radius: 10px;
            padding: 13px 16px;
            margin-bottom: 18px;
            font-size: 13px;
            background: #e7f8ef;
            color: #19744d;
        }

        /* Responsive */

        @media(max-width: 1100px) {
            .zo-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media(max-width: 650px) {
            .zo-websites {
                padding: 16px;
            }

            .zo-websites-header h1 {
                font-size: 24px;
            }

            .zo-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
            }

            .zo-stat-card {
                padding: 13px;
                gap: 9px;
            }

            .zo-stat-icon {
                width: 38px;
                height: 38px;
                min-width: 38px;
                font-size: 15px;
            }

            .zo-stat-value {
                font-size: 21px;
            }

            .zo-list-header {
                padding: 17px;
            }

            .zo-search {
                width: 100%;
            }

            .zo-website-list {
                padding: 10px;
            }

            .zo-website-card {
                padding: 15px;
            }

            .zo-website-identity {
                align-items: flex-start;
            }

            .zo-website-meta {
                gap: 8px;
            }

            .zo-meta-item {
                flex-basis: calc(50% - 8px);
                min-width: 0;
            }

            .zo-website-bottom {
                align-items: flex-start;
            }
        }
    </style>

    <div class="zo-websites">

        {{-- PAGE HEADER --}}
        <div class="zo-websites-header">

            <div>
                <div class="zo-breadcrumb">
                    Websites / All Websites
                </div>

                <h1>All Websites</h1>

                <p>
                    Manage your websites, technology stacks,
                    and deployment status.
                </p>
            </div>

            <a
                href="{{ route('panel.websites.create') }}"
                class="zo-add-btn"
            >
                <i class="fas fa-plus"></i>
                Add New Website
            </a>

        </div>

        {{-- SUCCESS --}}
        @if(session('success'))
            <div class="zo-feedback">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
            </div>
        @endif

        {{-- STATISTICS --}}
        <div class="zo-stats">

            <div class="zo-stat-card">
                <div class="zo-stat-icon blue">
                    <i class="fas fa-globe"></i>
                </div>

                <div>
                <span class="zo-stat-label">
                    Total Websites
                </span>
                    <div class="zo-stat-value">
                        {{ $totalWebsites }}
                    </div>
                </div>
            </div>

            <div class="zo-stat-card">
                <div class="zo-stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>

                <div>
                <span class="zo-stat-label">
                    Active
                </span>
                    <div class="zo-stat-value">
                        {{ $activeWebsites }}
                    </div>
                </div>
            </div>

            <div class="zo-stat-card">
                <div class="zo-stat-icon orange">
                    <i class="fas fa-clock"></i>
                </div>

                <div>
                <span class="zo-stat-label">
                    Pending / Deploying
                </span>
                    <div class="zo-stat-value">
                        {{ $pendingWebsites }}
                    </div>
                </div>
            </div>

            <div class="zo-stat-card">
                <div class="zo-stat-icon red">
                    <i class="fas fa-exclamation-circle"></i>
                </div>

                <div>
                <span class="zo-stat-label">
                    Failed
                </span>
                    <div class="zo-stat-value">
                        {{ $failedWebsites }}
                    </div>
                </div>
            </div>

        </div>

        {{-- WEBSITE LIST --}}
        <section class="zo-list-panel">

            <div class="zo-list-header">

                <div>
                    <h2>Your Websites</h2>

                    <p>
                        {{ $totalWebsites }}
                        {{ \Illuminate\Support\Str::plural('website', $totalWebsites) }}
                        registered in ZeitOps
                    </p>
                </div>

                <div class="zo-search">
                    <i class="fas fa-search"></i>
                    <input
                        id="zo-website-search"
                        type="search"
                        placeholder="Search websites..."
                        aria-label="Search websites"
                    >
                </div>

            </div>

            <div class="zo-website-list" id="zo-website-list">

                @forelse($websites as $website)

                    @php
                        $status = $website->status ?? 'pending_provisioning';

                        $statusLabel = $statusLabels[$status]
                            ?? ucwords(str_replace('_', ' ', $status));

                        $statusIcon = $statusIcons[$status]
                            ?? 'fa-info-circle';

                        $stackLabel = $stackNames[$website->stack]
                            ?? strtoupper($website->stack ?? 'Unknown');

                        $frameworkLabel = $frameworkNames[$website->framework]
                            ?? ucfirst($website->framework ?? 'Unknown');

                        $runtimeName = match ($website->stack) {
                            'lamp', 'lemp' => 'PHP',
                            'mern', 'mean', 'nodejs' => 'Node.js',
                            'python' => 'Python',
                            default => 'Runtime',
                        };

                        $runtimeVersion = $website->runtime_version
                            ?? $website->php_version
                            ?? null;

                        $repository = $website->repository_url;

                        $repositoryDisplay = $repository
                            ? preg_replace(
                                '~^(https://|git@)~',
                                '',
                                $repository
                              )
                            : null;
                    @endphp

                    <article
                        class="zo-website-card"
                        data-search="{{ strtolower(
                        $website->name . ' ' .
                        $website->domain . ' ' .
                        $stackLabel . ' ' .
                        $frameworkLabel . ' ' .
                        $statusLabel
                    ) }}"
                    >

                        {{-- TOP ROW --}}
                        <div class="zo-website-primary">

                            <div class="zo-website-identity">

                                <div class="zo-website-icon">
                                    <i class="fas fa-globe"></i>
                                </div>

                                <div style="min-width:0;">

                                    <h3 class="zo-website-name">
                                        {{ $website->name }}
                                    </h3>

                                    <div class="zo-website-domain">
                                        <i class="fas fa-link"></i>
                                        {{ $website->domain }}
                                    </div>

                                </div>

                            </div>

                            <span class="zo-status {{ in_array($status, array_keys($statusLabels), true) ? $status : '' }}">
                            <i class="fas {{ $statusIcon }}"></i>
                            {{ $statusLabel }}
                        </span>

                        </div>

                        {{-- WEBSITE DETAILS --}}
                        <div class="zo-website-meta">

                            <div class="zo-meta-item">
                                <small>Technology Stack</small>
                                <strong>
                                    <i class="fas fa-layer-group me-1"></i>
                                    {{ $stackLabel }}
                                </strong>
                            </div>

                            <div class="zo-meta-item">
                                <small>Framework</small>
                                <strong>{{ $frameworkLabel }}</strong>
                            </div>

                            <div class="zo-meta-item">
                                <small>Runtime</small>
                                <strong>
                                    {{ $runtimeName }}
                                    {{ $runtimeVersion ?? 'Not specified' }}
                                </strong>
                            </div>

                            <div class="zo-meta-item">
                                <small>Created</small>
                                <strong>
                                    {{ $website->created_at?->format('M d, Y') ?? 'Unknown' }}
                                </strong>
                            </div>

                        </div>

                        {{-- BOTTOM ROW --}}
                        <div class="zo-website-bottom">

                            <div class="zo-repository">
                                <i class="fab fa-git-alt"></i>

                                <span>
                                @if($repositoryDisplay)
                                        {{ $repositoryDisplay }}
                                    @else
                                        No Git repository configured
                                    @endif
                            </span>
                            </div>

                            <div class="zo-website-actions">

                                @if($repository)
                                    @php
                                        $gitUrl = null;

                                        if (preg_match(
                                            '~^https://(?:github\.com|gitlab\.com)/[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+(?:\.git)?$~',
                                            $repository
                                        )) {
                                            $gitUrl = $repository;
                                        } elseif (preg_match(
                                            '~^git@(github\.com|gitlab\.com):([A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+)(?:\.git)?$~',
                                            $repository,
                                            $gitMatches
                                        )) {
                                            $gitUrl = 'https://' . $gitMatches[1]
                                                . '/' . preg_replace('/\.git$/', '', $gitMatches[2]);
                                        }
                                    @endphp

                                    @if($gitUrl)
                                        <a
                                            href="{{ $gitUrl }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="zo-action-secondary"
                                        >
                                            <i class="fas fa-external-link-alt"></i>
                                            Repository
                                        </a>
                                    @endif
                                @endif

                            </div>

                        </div>

                    </article>

                @empty

                    <div class="zo-empty">

                        <div class="zo-empty-icon">
                            <i class="fas fa-globe"></i>
                        </div>

                        <h3>No websites created yet</h3>

                        <p>
                            Create your first website and start
                            managing it with ZeitOps.
                        </p>

                        <a
                            href="{{ route('panel.websites.create') }}"
                            class="zo-add-btn"
                        >
                            <i class="fas fa-plus"></i>
                            Create Website
                        </a>

                    </div>

                @endforelse

                @if($totalWebsites > 0)
                    <div
                        class="zo-empty"
                        id="zo-no-results"
                        hidden
                    >
                        <div class="zo-empty-icon">
                            <i class="fas fa-search"></i>
                        </div>

                        <h3>No matching websites</h3>

                        <p>Try searching with a different keyword.</p>
                    </div>
                @endif

            </div>

        </section>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const searchInput =
                document.getElementById('zo-website-search');

            const websiteCards = Array.from(
                document.querySelectorAll('.zo-website-card')
            );

            const noResults =
                document.getElementById('zo-no-results');

            if (!searchInput) return;

            searchInput.addEventListener('input', function () {

                const keyword = this.value.toLowerCase().trim();

                let visibleCount = 0;

                websiteCards.forEach(function (card) {

                    const searchableText = card.dataset.search || '';

                    const matches = searchableText.includes(keyword);

                    card.hidden = !matches;

                    if (matches) {
                        visibleCount++;
                    }

                });

                if (noResults) {
                    noResults.hidden = visibleCount !== 0;
                }

            });

        });
    </script>

@endsection
