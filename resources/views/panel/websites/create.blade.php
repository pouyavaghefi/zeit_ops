
@extends('panel.layouts.master')

@section('wrapper')

    <style>
        /* ==========================================
           ZEITOPS — WEBSITE CREATION
        ========================================== */

        .zo-create-page {
            padding: 28px;
            min-height: calc(100vh - 75px);
            background: #f5f7fc;
            color: #243149;
        }

        .zo-create-page * {
            box-sizing: border-box;
        }

        .zo-create-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 25px;
        }

        .zo-create-heading h1 {
            margin: 0 0 8px;
            color: #18283f;
            font-size: 29px;
            font-weight: 750;
        }

        .zo-create-heading p {
            margin: 0;
            color: #78879d;
            font-size: 14px;
        }

        .zo-crumb {
            color: #8997ae;
            font-size: 12px;
            margin-bottom: 9px;
        }

        .zo-page-tag {
            display: inline-flex;
            gap: 8px;
            align-items: center;
            padding: 9px 13px;
            border-radius: 30px;
            background: #e8efff;
            color: #315ce7;
            font-size: 12px;
            font-weight: 650;
        }

        /* Layout */

        .zo-create-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 290px;
            align-items: start;
            gap: 22px;
        }

        .zo-create-main {
            min-width: 0;
        }

        .zo-create-card,
        .zo-summary {
            background: #fff;
            border: 1px solid #e7ecf5;
            border-radius: 16px;
            box-shadow: 0 5px 22px rgba(27, 50, 100, .045);
            overflow: hidden;
        }

        .zo-create-card {
            margin-bottom: 20px;
        }

        .zo-card-head {
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 21px 24px;
            border-bottom: 1px solid #edf0f5;
        }

        .zo-card-icon {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg,#276eff,#5142df);
            color: white;
            font-size: 18px;
            flex-shrink: 0;
        }

        .zo-card-head h2 {
            font-size: 17px;
            font-weight: 700;
            margin: 0 0 5px;
            color: #243149;
        }

        .zo-card-head p {
            font-size: 12px;
            color: #8694aa;
            margin: 0;
        }

        .zo-card-body {
            padding: 24px;
        }

        /* Form */

        .zo-field-grid {
            display: grid;
            grid-template-columns: repeat(2,minmax(0,1fr));
            gap: 21px;
        }

        .zo-field {
            min-width: 0;
        }

        .zo-field-full {
            grid-column: 1 / -1;
        }

        .zo-field label {
            display: block;
            font-size: 13px;
            font-weight: 650;
            margin-bottom: 9px;
            color: #31435c;
        }

        .zo-required {
            color: #e35170;
        }

        .zo-field input,
        .zo-field select {
            display: block;
            width: 100%;
            min-height: 47px;
            padding: 11px 14px;
            border: 1px solid #dce4f0;
            border-radius: 9px;
            background: #fff;
            color: #243149;
            font-size: 13px;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .zo-field select {
            cursor: pointer;
        }

        .zo-field input:focus,
        .zo-field select:focus {
            border-color: #3978fb;
            box-shadow: 0 0 0 3px rgba(57,120,251,.12);
        }

        .zo-field input::placeholder {
            color: #a1adbf;
        }

        .zo-field small,
        .zo-hint {
            display: block;
            margin-top: 7px;
            color: #8795a9;
            font-size: 11px;
            line-height: 1.65;
        }

        .zo-field .is-invalid {
            border-color: #e96380;
        }

        .zo-error {
            color: #c83c58 !important;
            font-size: 11px;
            margin-top: 6px;
        }

        /* Live stack details */

        .zo-stack-info {
            display: flex;
            gap: 15px;
            align-items: flex-start;
            margin-top: 20px;
            padding: 17px;
            background: linear-gradient(135deg,#f0f6ff,#f7f5ff);
            border: 1px solid #dce7ff;
            border-radius: 12px;
            transition: background .2s ease;
        }

        .zo-stack-symbol {
            display: flex;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #dce9ff;
            color: #2d65e9;
            font-size: 23px;
        }

        .zo-stack-info h3 {
            margin: 1px 0 6px;
            font-size: 15px;
            font-weight: 700;
        }

        .zo-stack-info p {
            color: #61738d;
            font-size: 12px;
            line-height: 1.6;
            margin: 0 0 8px;
        }

        .zo-tech-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .zo-tech-tags span {
            padding: 5px 9px;
            color: #3560b5;
            background: #fff;
            border: 1px solid #e0e9fa;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 650;
        }

        /* Repository */

        .zo-repo-note {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 14px;
            border-radius: 9px;
            background: #f6f8fc;
            color: #718198;
            font-size: 12px;
            line-height: 1.7;
            margin-top: 19px;
        }

        .zo-repo-note i {
            color: #476eeb;
            margin-top: 3px;
        }

        /* Summary */

        .zo-summary {
            position: sticky;
            top: 20px;
        }

        .zo-summary-heading {
            padding: 20px;
            border-bottom: 1px solid #eaf0f7;
        }

        .zo-summary-heading h3 {
            margin: 0 0 5px;
            font-size: 16px;
            font-weight: 700;
        }

        .zo-summary-heading p {
            margin: 0;
            font-size: 11px;
            color: #8997ac;
        }

        .zo-summary-body {
            padding: 20px;
        }

        .zo-summary-row {
            padding: 11px 0;
            border-bottom: 1px solid #f0f2f7;
        }

        .zo-summary-row:last-child {
            border-bottom: none;
        }

        .zo-summary-row small {
            display: block;
            color: #8c99ad;
            font-size: 11px;
            margin-bottom: 5px;
        }

        .zo-summary-row strong {
            display: block;
            font-size: 12px;
            font-weight: 650;
            color: #32425d;
            overflow-wrap: anywhere;
        }

        .zo-summary-status {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 10px;
            background: #fff5e5;
            color: #b67a13;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 650;
        }

        .zo-summary-foot {
            padding: 16px 20px;
            background: #f8faff;
            border-top: 1px solid #eaf0f7;
            color: #8190a6;
            font-size: 11px;
            line-height: 1.7;
        }

        /* Footer */

        .zo-create-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            flex-wrap: wrap;
            padding: 20px 24px;
            background: #fcfdff;
            border-top: 1px solid #edf0f5;
        }

        .zo-create-actions p {
            margin: 0;
            font-size: 11px;
            color: #8693a7;
        }

        .zo-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 20px;
            min-height: 44px;
            border: none;
            border-radius: 9px;
            background: linear-gradient(135deg,#1670ff,#4154e5);
            color: #fff;
            font-size: 13px;
            font-weight: 650;
            cursor: pointer;
            transition: transform .2s, box-shadow .2s;
        }

        .zo-submit:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(25,105,240,.2);
        }

        .zo-submit:disabled {
            cursor: not-allowed;
            opacity: .5;
        }

        .zo-alert {
            border-radius: 10px;
            padding: 15px 18px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .zo-alert-success {
            background: #e8f9f1;
            color: #176c49;
        }

        .zo-alert-error {
            background: #fff0f3;
            color: #ad344d;
        }

        /* Responsive */

        @media(max-width:1150px) {
            .zo-create-layout {
                grid-template-columns: 1fr;
            }

            .zo-summary {
                position: static;
            }
        }

        @media(max-width:650px) {
            .zo-create-page {
                padding: 16px;
            }

            .zo-create-heading h1 {
                font-size: 24px;
            }

            .zo-field-grid {
                grid-template-columns: 1fr;
                gap: 17px;
            }

            .zo-field-full {
                grid-column: auto;
            }

            .zo-card-head,
            .zo-card-body,
            .zo-create-actions {
                padding: 18px;
            }
        }
    </style>

    <div class="zo-create-page">

        {{-- HEADING --}}
        <div class="zo-create-heading">
            <div>
                <div class="zo-crumb">
                    Websites / Add New Website
                </div>

                <h1>Add New Website</h1>

                <p>
                    Configure your application, choose a stack,
                    and connect a Git repository.
                </p>
            </div>

            <span class="zo-page-tag">
            <i class="fas fa-layer-group"></i>
            Multi-Stack Support
        </span>
        </div>

        {{-- SUCCESS MESSAGE --}}
        @if(session('success'))
            <div class="zo-alert zo-alert-success">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
            </div>
        @endif

        {{-- VALIDATION ERRORS --}}
        @if($errors->any())
            <div class="zo-alert zo-alert-error">
                <strong>Please correct the following errors:</strong>

                <ul class="mt-2 mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            id="zo-website-form"
            method="POST"
            action="{{ route('panel.websites.store') }}"
        >
            @csrf

            <div class="zo-create-layout">

                <div class="zo-create-main">

                    {{-- WEBSITE DETAILS --}}
                    <section class="zo-create-card">

                        <div class="zo-card-head">
                            <div class="zo-card-icon">
                                <i class="fas fa-globe"></i>
                            </div>

                            <div>
                                <h2>Website Details</h2>
                                <p>
                                    Select the destination server
                                    and define your website.
                                </p>
                            </div>
                        </div>

                        <div class="zo-card-body">

                            <div class="zo-field-grid">

                                {{-- SERVER --}}
                                <div class="zo-field zo-field-full">

                                    <label for="server_id">
                                        Target Server
                                        <span class="zo-required">*</span>
                                    </label>

                                    <select
                                        id="server_id"
                                        name="server_id"
                                        required
                                    >
                                        <option value="">
                                            Select a server
                                        </option>

                                        @foreach($servers as $server)
                                            <option
                                                value="{{ $server['id'] }}"
                                                @disabled(!$server['selectable'])
                                                @selected(old('server_id') === $server['id'])
                                            >
                                                {{ $server['name'] }}
                                                — {{ $server['ip'] }}

                                                ({{ $server['selectable']
                                                ? 'Connected'
                                                : ucfirst($server['status']) . ' - Unavailable' }})
                                            </option>
                                        @endforeach
                                    </select>

                                    <small>
                                        Only connected servers can host new websites.
                                        Disconnected or pending servers are shown
                                        but cannot be selected.
                                    </small>

                                    @error('server_id')
                                    <small class="zo-error">{{ $message }}</small>
                                    @enderror

                                </div>

                                {{-- NAME --}}
                                <div class="zo-field">

                                    <label for="website_name">
                                        Website Name
                                        <span class="zo-required">*</span>
                                    </label>

                                    <input
                                        id="website_name"
                                        name="name"
                                        type="text"
                                        placeholder="My Application"
                                        value="{{ old('name') }}"
                                        maxlength="120"
                                        required
                                    >

                                </div>

                                {{-- DOMAIN --}}
                                <div class="zo-field">

                                    <label for="website_domain">
                                        Domain Name
                                        <span class="zo-required">*</span>
                                    </label>

                                    <input
                                        id="website_domain"
                                        name="domain"
                                        type="text"
                                        placeholder="app.example.com"
                                        value="{{ old('domain') }}"
                                        maxlength="253"
                                        required
                                    >

                                    <small>
                                        Enter your domain without https://
                                    </small>

                                </div>

                            </div>

                        </div>

                    </section>

                    {{-- TECHNOLOGY STACK --}}
                    <section class="zo-create-card">

                        <div class="zo-card-head">
                            <div class="zo-card-icon">
                                <i class="fas fa-layer-group"></i>
                            </div>

                            <div>
                                <h2>Technology Stack</h2>
                                <p>
                                    Choose the technologies required
                                    by your application.
                                </p>
                            </div>
                        </div>

                        <div class="zo-card-body">

                            <div class="zo-field-grid">

                                {{-- STACK --}}
                                <div class="zo-field zo-field-full">

                                    <label for="stack">
                                        Technology Stack
                                        <span class="zo-required">*</span>
                                    </label>

                                    <select id="stack" name="stack" required>
                                        <option value="lamp">
                                            LAMP Stack
                                        </option>

                                        <option value="lemp">
                                            LEMP Stack
                                        </option>

                                        <option value="mern">
                                            MERN Stack
                                        </option>

                                        <option value="mean">
                                            MEAN Stack
                                        </option>

                                        <option value="python">
                                            Python Stack
                                        </option>

                                        <option value="nodejs">
                                            Node.js Stack
                                        </option>
                                    </select>

                                    <small id="zo-stack-description">
                                        LAMP - Linux, Apache, MySQL, PHP
                                    </small>

                                </div>

                                {{-- FRAMEWORK --}}
                                <div class="zo-field">

                                    <label for="framework">
                                        Application Framework
                                        <span class="zo-required">*</span>
                                    </label>

                                    <select
                                        id="framework"
                                        name="framework"
                                        required
                                    ></select>

                                    <small id="zo-framework-description">
                                        Choose a framework.
                                    </small>

                                </div>

                                {{-- RUNTIME --}}
                                <div class="zo-field">

                                    <label for="runtime_version" id="runtime_label">
                                        PHP Version
                                        <span class="zo-required">*</span>
                                    </label>

                                    <select
                                        id="runtime_version"
                                        name="runtime_version"
                                        required
                                    ></select>

                                    <small id="zo-runtime-description">
                                        Select a runtime version.
                                    </small>

                                </div>

                            </div>

                            {{-- LIVE STACK INFORMATION --}}
                            <div class="zo-stack-info">

                                <div class="zo-stack-symbol">
                                    <i class="fas fa-server" id="zo-stack-icon"></i>
                                </div>

                                <div>
                                    <h3 id="zo-stack-title">
                                        LAMP Stack
                                    </h3>

                                    <p id="zo-stack-explanation">
                                        A traditional Linux-based stack for
                                        PHP websites and applications.
                                    </p>

                                    <div
                                        id="zo-tech-tags"
                                        class="zo-tech-tags"
                                    ></div>
                                </div>

                            </div>

                        </div>

                    </section>

                    {{-- GIT REPOSITORY --}}
                    <section class="zo-create-card">

                        <div class="zo-card-head">
                            <div class="zo-card-icon">
                                <i class="fas fa-code-branch"></i>
                            </div>

                            <div>
                                <h2>Git Repository</h2>
                                <p>
                                    Configure the source repository
                                    for future deployments.
                                </p>
                            </div>
                        </div>

                        <div class="zo-card-body">

                            <div class="zo-field-grid">

                                <div class="zo-field zo-field-full">

                                    <label for="repository_url">
                                        Repository URL
                                    </label>

                                    <input
                                        id="repository_url"
                                        type="text"
                                        name="repository_url"
                                        maxlength="500"
                                        placeholder="https://github.com/username/repository.git"
                                        value="{{ old('repository_url') }}"
                                    >

                                    <small>
                                        GitHub and GitLab HTTPS or SSH repository URLs.
                                    </small>

                                </div>

                                <div class="zo-field">

                                    <label for="repository_branch">
                                        Deployment Branch
                                        <span class="zo-required">*</span>
                                    </label>

                                    <input
                                        id="repository_branch"
                                        type="text"
                                        name="repository_branch"
                                        maxlength="120"
                                        value="{{ old('repository_branch','main') }}"
                                        placeholder="main"
                                        required
                                    >

                                </div>

                                <div class="zo-field">

                                    <label>
                                        Deployment Method
                                    </label>

                                    <input
                                        type="text"
                                        value="Git-based Deployment"
                                        disabled
                                    >

                                    <small>
                                        Deployment will run on the selected VPS.
                                    </small>

                                </div>

                            </div>

                            <div class="zo-repo-note">
                                <i class="fas fa-info-circle"></i>
                                <div>
                                    Creating the website stores its configuration.
                                    Actual installation, Git cloning and deployment
                                    will be performed through the deployment engine.
                                </div>
                            </div>

                        </div>

                        <div class="zo-create-actions">

                            <p>
                                <i class="fas fa-shield-alt me-1"></i>
                                Only servers belonging to your account are eligible.
                            </p>

                            <button
                                type="submit"
                                class="zo-submit"
                                id="zo-submit-button"
                                @disabled(!$servers->contains('selectable', true))
                            >
                                <i class="fas fa-plus-circle"></i>
                                Create Website
                            </button>

                        </div>

                    </section>

                </div>

                {{-- LIVE SUMMARY --}}
                <aside class="zo-summary">

                    <div class="zo-summary-heading">
                        <h3>
                            <i class="fas fa-clipboard-check me-2"></i>
                            Configuration Summary
                        </h3>

                        <p>
                            Updates automatically as you configure your website.
                        </p>
                    </div>

                    <div class="zo-summary-body">

                        <div class="zo-summary-row">
                            <small>Website</small>
                            <strong id="zo-summary-name">
                                Not specified
                            </strong>
                        </div>

                        <div class="zo-summary-row">
                            <small>Domain</small>
                            <strong id="zo-summary-domain">
                                Not specified
                            </strong>
                        </div>

                        <div class="zo-summary-row">
                            <small>Target Server</small>
                            <strong id="zo-summary-server">
                                Not selected
                            </strong>
                        </div>

                        <div class="zo-summary-row">
                            <small>Stack</small>
                            <strong id="zo-summary-stack">
                                LAMP
                            </strong>
                        </div>

                        <div class="zo-summary-row">
                            <small>Framework</small>
                            <strong id="zo-summary-framework">
                                Laravel
                            </strong>
                        </div>

                        <div class="zo-summary-row">
                            <small>Runtime</small>
                            <strong id="zo-summary-runtime">
                                PHP 8.3
                            </strong>
                        </div>

                        <div class="zo-summary-row">
                            <small>Repository</small>
                            <strong id="zo-summary-repository">
                                Not connected
                            </strong>
                        </div>

                        <div class="zo-summary-row">
                            <small>Initial Status</small>

                            <span class="zo-summary-status">
                            <i class="fas fa-clock"></i>
                            Pending Provisioning
                        </span>
                        </div>

                    </div>

                    <div class="zo-summary-foot">
                        <i class="fas fa-info-circle me-1"></i>
                        Review your configuration before creating the website.
                    </div>

                </aside>

            </div>

        </form>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            'use strict';

            /*
            |--------------------------------------------------------------------------
            | Available Technology Stacks
            |--------------------------------------------------------------------------
            */

            const stacks = {
                lamp: {
                    title: 'LAMP Stack',
                    short: 'Linux, Apache, MySQL, PHP',
                    description:
                        'A traditional server stack for Laravel, Symfony, WordPress, and PHP applications.',
                    icon: 'fas fa-server',
                    technologies: [
                        'Linux', 'Apache', 'MySQL', 'PHP', 'Composer'
                    ],
                    frameworks: [
                        {
                            value: 'laravel',
                            label: 'Laravel',
                            description: 'Modern PHP application framework'
                        },
                        {
                            value: 'symfony',
                            label: 'Symfony',
                            description: 'Component-based PHP framework'
                        },
                        {
                            value: 'wordpress',
                            label: 'WordPress',
                            description: 'PHP content management system'
                        },
                        {
                            value: 'php',
                            label: 'Plain PHP',
                            description: 'Custom PHP application'
                        }
                    ],
                    runtimeLabel: 'PHP Version',
                    runtimeName: 'PHP',
                    versions: ['8.3', '8.4']
                },

                lemp: {
                    title: 'LEMP Stack',
                    short: 'Linux, Nginx, MySQL, PHP',
                    description:
                        'A PHP deployment stack using Nginx instead of Apache.',
                    icon: 'fas fa-bolt',
                    technologies: [
                        'Linux', 'Nginx', 'MySQL', 'PHP-FPM', 'Composer'
                    ],
                    frameworks: [
                        {
                            value: 'laravel',
                            label: 'Laravel',
                            description: 'Modern PHP application framework'
                        },
                        {
                            value: 'symfony',
                            label: 'Symfony',
                            description: 'Component-based PHP framework'
                        },
                        {
                            value: 'wordpress',
                            label: 'WordPress',
                            description: 'PHP content management system'
                        },
                        {
                            value: 'php',
                            label: 'Plain PHP',
                            description: 'Custom PHP application'
                        }
                    ],
                    runtimeLabel: 'PHP Version',
                    runtimeName: 'PHP',
                    versions: ['8.3', '8.4']
                },

                mern: {
                    title: 'MERN Stack',
                    short: 'MongoDB, Express, React, Node.js',
                    description:
                        'A JavaScript full-stack environment built around React, Express, and MongoDB.',
                    icon: 'fab fa-react',
                    technologies: [
                        'MongoDB', 'Express', 'React', 'Node.js', 'npm'
                    ],
                    frameworks: [
                        {
                            value: 'react-express',
                            label: 'React + Express',
                            description: 'React frontend with an Express API'
                        },
                        {
                            value: 'next-express',
                            label: 'Next.js + Express',
                            description: 'React-based Next.js frontend with an Express backend'
                        }
                    ],
                    runtimeLabel: 'Node.js Version',
                    runtimeName: 'Node.js',
                    versions: ['20', '22', '24']
                },

                mean: {
                    title: 'MEAN Stack',
                    short: 'MongoDB, Express, Angular, Node.js',
                    description:
                        'A full-stack JavaScript architecture using Angular and Express.',
                    icon: 'fab fa-angular',
                    technologies: [
                        'MongoDB', 'Express', 'Angular', 'Node.js', 'npm'
                    ],
                    frameworks: [
                        {
                            value: 'angular-express',
                            label: 'Angular + Express',
                            description: 'Angular frontend with an Express API'
                        }
                    ],
                    runtimeLabel: 'Node.js Version',
                    runtimeName: 'Node.js',
                    versions: ['20', '22', '24']
                },

                python: {
                    title: 'Python Stack',
                    short: 'Python, Django / Flask / FastAPI',
                    description:
                        'Deploy Python web applications and APIs with an application server and reverse proxy.',
                    icon: 'fab fa-python',
                    technologies: [
                        'Python', 'pip', 'Gunicorn/Uvicorn', 'Nginx'
                    ],
                    frameworks: [
                        {
                            value: 'django',
                            label: 'Django',
                            description: 'Full-featured Python web framework'
                        },
                        {
                            value: 'flask',
                            label: 'Flask',
                            description: 'Lightweight Python web framework'
                        },
                        {
                            value: 'fastapi',
                            label: 'FastAPI',
                            description: 'Modern framework for Python APIs'
                        }
                    ],
                    runtimeLabel: 'Python Version',
                    runtimeName: 'Python',
                    versions: ['3.11', '3.12', '3.13']
                },

                nodejs: {
                    title: 'Node.js Stack',
                    short: 'Node.js, Express / NestJS / Next.js',
                    description:
                        'Run JavaScript and TypeScript applications using Node.js and a reverse proxy.',
                    icon: 'fab fa-node-js',
                    technologies: [
                        'Node.js', 'npm', 'Process Manager', 'Nginx'
                    ],
                    frameworks: [
                        {
                            value: 'express',
                            label: 'Express.js',
                            description: 'Minimal Node.js web framework'
                        },
                        {
                            value: 'nestjs',
                            label: 'NestJS',
                            description: 'Structured Node.js and TypeScript framework'
                        },
                        {
                            value: 'nextjs',
                            label: 'Next.js',
                            description: 'React framework with server-side rendering'
                        }
                    ],
                    runtimeLabel: 'Node.js Version',
                    runtimeName: 'Node.js',
                    versions: ['20', '22', '24']
                }
            };

            /*
            |--------------------------------------------------------------------------
            | DOM Elements
            |--------------------------------------------------------------------------
            */

            const form = document.getElementById('zo-website-form');

            const stackSelect = document.getElementById('stack');
            const frameworkSelect = document.getElementById('framework');
            const runtimeSelect = document.getElementById('runtime_version');

            const runtimeLabel = document.getElementById('runtime_label');

            const stackDescription =
                document.getElementById('zo-stack-description');

            const frameworkDescription =
                document.getElementById('zo-framework-description');

            const runtimeDescription =
                document.getElementById('zo-runtime-description');

            const stackTitle =
                document.getElementById('zo-stack-title');

            const stackExplanation =
                document.getElementById('zo-stack-explanation');

            const stackIcon =
                document.getElementById('zo-stack-icon');

            const techTags =
                document.getElementById('zo-tech-tags');

            const serverSelect = document.getElementById('server_id');
            const websiteName = document.getElementById('website_name');
            const websiteDomain = document.getElementById('website_domain');
            const repositoryInput = document.getElementById('repository_url');

            const summary = {
                name: document.getElementById('zo-summary-name'),
                domain: document.getElementById('zo-summary-domain'),
                server: document.getElementById('zo-summary-server'),
                stack: document.getElementById('zo-summary-stack'),
                framework: document.getElementById('zo-summary-framework'),
                runtime: document.getElementById('zo-summary-runtime'),
                repository: document.getElementById('zo-summary-repository')
            };

            /*
            |--------------------------------------------------------------------------
            | Existing Form Values (Laravel Validation Redirect)
            |--------------------------------------------------------------------------
            */

            const previous = {
                stack: @json(old('stack', 'lamp')),
                framework: @json(old('framework')),
                runtime: @json(old('runtime_version'))
            };

            /*
            |--------------------------------------------------------------------------
            | Helpers
            |--------------------------------------------------------------------------
            */

            function setOptions(select, items, preferredValue) {
                select.replaceChildren();

                items.forEach(function (item) {
                    const option = document.createElement('option');

                    option.value = item.value;
                    option.textContent = item.label;

                    select.appendChild(option);
                });

                const hasPreferred = items.some(function (item) {
                    return item.value === preferredValue;
                });

                if (hasPreferred) {
                    select.value = preferredValue;
                } else if (items.length) {
                    select.value = items[0].value;
                }
            }

            function selectedText(select) {
                if (!select.value) return null;

                return select.selectedOptions[0]?.textContent?.trim() || null;
            }

            function showSummaryValue(element, value, fallback) {
                element.textContent = value || fallback;
            }

            /*
            |--------------------------------------------------------------------------
            | Update Stack + Framework + Runtime
            |--------------------------------------------------------------------------
            */

            function updateStack(preferredFramework, preferredRuntime) {

                const config = stacks[stackSelect.value];

                if (!config) return;

                // Frameworks
                setOptions(
                    frameworkSelect,
                    config.frameworks.map(function (item) {
                        return {
                            value: item.value,
                            label: item.label
                        };
                    }),
                    preferredFramework
                );

                // Runtime versions
                setOptions(
                    runtimeSelect,
                    config.versions.map(function (version) {
                        return {
                            value: version,
                            label: config.runtimeName + ' ' + version
                        };
                    }),
                    preferredRuntime
                );

                // Dynamic labels
                runtimeLabel.replaceChildren();

                const labelText = document.createTextNode(
                    config.runtimeLabel + ' '
                );

                const required = document.createElement('span');
                required.className = 'zo-required';
                required.textContent = '*';

                runtimeLabel.append(labelText, required);

                stackDescription.textContent = config.short;

                runtimeDescription.textContent =
                    'The selected application will use ' +
                    config.runtimeName + ' ' + runtimeSelect.value + '.';

                stackTitle.textContent = config.title;
                stackExplanation.textContent = config.description;

                stackIcon.className = config.icon;

                // Technology badges
                techTags.replaceChildren();

                config.technologies.forEach(function (technology) {
                    const tag = document.createElement('span');
                    tag.textContent = technology;
                    techTags.appendChild(tag);
                });

                updateFramework();
                updateSummary();
            }

            /*
            |--------------------------------------------------------------------------
            | Framework Information
            |--------------------------------------------------------------------------
            */

            function updateFramework() {
                const config = stacks[stackSelect.value];

                if (!config) return;

                const framework = config.frameworks.find(function (item) {
                    return item.value === frameworkSelect.value;
                });

                frameworkDescription.textContent = framework
                    ? framework.description
                    : 'Select a framework.';

                updateSummary();
            }

            /*
            |--------------------------------------------------------------------------
            | Live Summary
            |--------------------------------------------------------------------------
            */

            function updateSummary() {
                const config = stacks[stackSelect.value];

                if (!config) return;

                showSummaryValue(
                    summary.name,
                    websiteName.value.trim(),
                    'Not specified'
                );

                showSummaryValue(
                    summary.domain,
                    websiteDomain.value.trim(),
                    'Not specified'
                );

                showSummaryValue(
                    summary.server,
                    selectedText(serverSelect),
                    'Not selected'
                );

                summary.stack.textContent = config.title;

                showSummaryValue(
                    summary.framework,
                    selectedText(frameworkSelect),
                    'Not selected'
                );

                showSummaryValue(
                    summary.runtime,
                    selectedText(runtimeSelect),
                    'Not selected'
                );

                showSummaryValue(
                    summary.repository,
                    repositoryInput.value.trim(),
                    'Not connected'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Events
            |--------------------------------------------------------------------------
            */

            stackSelect.addEventListener('change', function () {
                updateStack(null, null);
            });

            frameworkSelect.addEventListener('change', updateFramework);

            runtimeSelect.addEventListener('change', function () {
                const config = stacks[stackSelect.value];

                runtimeDescription.textContent =
                    'The selected application will use ' +
                    config.runtimeName + ' ' + runtimeSelect.value + '.';

                updateSummary();
            });

            [
                serverSelect,
                websiteName,
                websiteDomain,
                repositoryInput
            ].forEach(function (element) {

                const eventName =
                    element.tagName === 'SELECT' ? 'change' : 'input';

                element.addEventListener(eventName, updateSummary);
            });

            /*
            |--------------------------------------------------------------------------
            | Prevent Accidental Double Submission
            |--------------------------------------------------------------------------
            */

            form.addEventListener('submit', function () {
                const submitButton =
                    document.getElementById('zo-submit-button');

                if (!form.checkValidity() || !submitButton || submitButton.disabled) {
                    return;
                }

                submitButton.disabled = true;
                submitButton.innerHTML =
                    '<i class="fas fa-spinner fa-spin"></i> Creating Website...';
            });

            /*
            |--------------------------------------------------------------------------
            | Initialize
            |--------------------------------------------------------------------------
            */

            stackSelect.value = stacks[previous.stack]
                ? previous.stack
                : 'lamp';

            updateStack(previous.framework, previous.runtime);

        });
    </script>

@endsection
