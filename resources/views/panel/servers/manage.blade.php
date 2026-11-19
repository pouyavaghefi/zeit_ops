
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

        $hostVerified = (bool) ($server['host_verified'] ?? false);
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

                            This page displays your registered VPS details.
                            Remote inspection, installation, monitoring, and
                            security operations require the provisioning
                            backend to be implemented.
                        </div>

                    </div>

                </section>

            </div>

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

@endsection
