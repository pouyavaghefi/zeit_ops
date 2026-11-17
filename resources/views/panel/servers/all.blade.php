
@extends('panel.layouts.master')

@section('wrapper')

    <style>
        /* ==========================================
           ZEITOPS — ALL SERVERS
        ========================================== */

        .zo-servers-page {
            padding: 28px;
            min-height: calc(100vh - 75px);
            background: #f5f7fc;
            color: #253047;
        }

        /* Page heading */

        .zo-page-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 18px;
            margin-bottom: 26px;
        }

        .zo-breadcrumb {
            color: #8793a9;
            font-size: 12px;
            margin-bottom: 8px;
        }

        .zo-page-heading h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 6px;
            color: #202b40;
        }

        .zo-page-heading p {
            color: #78869d;
            font-size: 14px;
            margin: 0;
        }

        /* Buttons */

        .zo-btn {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 9px;
            padding: 11px 17px;
            border-radius: 9px;
            border: 1px solid transparent;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            transition: .2s ease;
        }

        .zo-btn-primary {
            color: #fff;
            background: #1269fa;
        }

        .zo-btn-primary:hover {
            color: #fff;
            background: #0758df;
        }

        .zo-btn-outline {
            color: #2852b2;
            background: #fff;
            border-color: #dce5f2;
        }

        .zo-btn-outline:hover {
            color: #1451c5;
            background: #eef4ff;
        }

        /* Statistics */

        .zo-server-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 26px;
        }

        .zo-stat-card {
            position: relative;
            background: #fff;
            border-radius: 15px;
            border-left: 4px solid #4875fa;
            padding: 23px;
            min-height: 135px;
            box-shadow: 0 5px 20px rgba(25, 44, 85, .055);
        }

        .zo-stat-total {
            border-left-color: #4865fa;
        }

        .zo-stat-connected {
            border-left-color: #21c4e6;
        }

        .zo-stat-pending {
            border-left-color: #f4b328;
        }

        .zo-stat-failed {
            border-left-color: #f04a79;
        }

        .zo-stat-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 43px;
            height: 43px;
            border-radius: 12px;
            background: #eff4ff;
            color: #8ba9ed;
            font-size: 19px;
        }

        .zo-stat-connected .zo-stat-icon {
            background: #e9faff;
            color: #19b5d7;
        }

        .zo-stat-pending .zo-stat-icon {
            background: #fff7e6;
            color: #e6a11c;
        }

        .zo-stat-failed .zo-stat-icon {
            background: #fff0f4;
            color: #e85b82;
        }

        .zo-stat-number {
            color: #1c293d;
            font-size: 35px;
            font-weight: 700;
            line-height: 1.15;
        }

        .zo-stat-label {
            color: #68788f;
            margin-top: 9px;
            font-size: 13px;
        }

        /* Panels */

        .zo-server-panel,
        .zo-activity-panel {
            background: #fff;
            border: 1px solid #edf0f6;
            border-radius: 15px;
            margin-bottom: 24px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(25, 44, 85, .045);
        }

        .zo-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            padding: 24px 26px;
            border-bottom: 1px solid #edf0f6;
        }

        .zo-panel-header h2 {
            display: flex;
            align-items: center;
            gap: 11px;
            color: #202b40;
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 7px;
        }

        .zo-panel-header h2 i {
            color: #416bef;
            font-size: 18px;
        }

        .zo-panel-header p {
            color: #7a889e;
            font-size: 13px;
            margin: 0;
        }

        /* Server list */

        .zo-server-list {
            display: flex;
            flex-direction: column;
            gap: 13px;
            padding: 22px;
        }

        .zo-server-item {
            display: grid;
            grid-template-columns:
            minmax(220px, 2fr)
            minmax(145px, 1fr)
            minmax(145px, 1fr)
            minmax(145px, auto);
            align-items: center;
            gap: 20px;
            padding: 19px 20px;
            border: 1px solid #e4eaf4;
            border-radius: 12px;
            background: #fff;
            transition: border-color .2s, box-shadow .2s;
        }

        .zo-server-item:hover {
            border-color: #b9cafa;
            box-shadow: 0 6px 18px rgba(41, 81, 165, .07);
        }

        /* Server identity */

        .zo-server-identity {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .zo-server-avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 48px;
            width: 48px;
            height: 48px;
            border-radius: 13px;
            background: linear-gradient(135deg, #216dfb, #4e45d9);
            color: #fff;
            font-size: 20px;
        }

        .zo-server-details {
            min-width: 0;
        }

        .zo-server-details h3 {
            color: #1e2d47;
            font-size: 15px;
            font-weight: 700;
            margin: 0 0 9px;
            overflow-wrap: anywhere;
        }

        .zo-server-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px 13px;
            color: #62728c;
            font-size: 12px;
        }

        .zo-server-meta span {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .zo-server-meta i {
            color: #8ca0c6;
        }

        .zo-server-id {
            margin-top: 8px;
            color: #a0acc0;
            font-size: 11px;
            overflow-wrap: anywhere;
        }

        /* Status */

        .zo-server-status-section {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 11px;
        }

        .zo-status {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 11px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .zo-status-connected {
            background: #e8faf1;
            color: #15945e;
        }

        .zo-status-pending {
            background: #fff4dc;
            color: #b77a11;
        }

        .zo-status-failed {
            background: #fff0f2;
            color: #d3445b;
        }

        .zo-host-status {
            display: flex;
            align-items: center;
            gap: 7px;
            color: #718096;
            font-size: 11px;
        }

        .zo-text-success {
            color: #1aab6d;
        }

        .zo-text-muted {
            color: #a4afbf;
        }

        /* Connection time */

        .zo-server-time {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .zo-time-label {
            color: #8491a5;
            font-size: 11px;
        }

        .zo-time-value {
            color: #2a3850;
            font-size: 13px;
            font-weight: 600;
        }

        .zo-time-secondary {
            color: #8491a5;
            font-size: 11px;
        }

        /* Actions */

        .zo-server-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }

        .zo-management-pending {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: #8291aa;
            font-size: 12px;
            text-align: right;
        }

        /* Error details */

        .zo-server-error {
            grid-column: 1 / -1;
            display: flex;
            align-items: flex-start;
            gap: 9px;
            padding: 12px 14px;
            border-radius: 8px;
            background: #fff3f4;
            color: #c13b51;
            font-size: 12px;
            overflow-wrap: anywhere;
        }

        /* Empty state */

        .zo-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
            padding: 65px 24px;
            text-align: center;
        }

        .zo-empty-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 76px;
            height: 76px;
            border-radius: 20px;
            background: #eff4ff;
            color: #4b78ee;
            font-size: 32px;
        }

        .zo-empty-state h3 {
            color: #202b40;
            font-size: 21px;
            font-weight: 700;
            margin: 0;
        }

        .zo-empty-state p {
            color: #8290a7;
            max-width: 420px;
            line-height: 1.7;
            margin: 0 0 6px;
        }

        /* Activity */

        .zo-activity-empty {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            padding: 32px 20px;
            color: #8d9aaf;
            font-size: 13px;
        }

        .zo-activity-empty i {
            color: #a9b8d4;
            font-size: 20px;
        }

        /* Responsive */

        @media (max-width: 1350px) {
            .zo-server-item {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .zo-server-actions {
                justify-content: flex-start;
            }
        }

        @media (max-width: 991px) {
            .zo-servers-page {
                padding: 18px;
            }

            .zo-server-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .zo-page-heading h1 {
                font-size: 25px;
            }
        }

        @media (max-width: 575px) {
            .zo-page-heading,
            .zo-panel-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .zo-server-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
            }

            .zo-stat-card {
                padding: 16px;
                min-height: 115px;
            }

            .zo-stat-number {
                font-size: 29px;
            }

            .zo-stat-icon {
                top: 13px;
                right: 12px;
                width: 34px;
                height: 34px;
                font-size: 15px;
            }

            .zo-server-list {
                padding: 12px;
            }

            .zo-server-item {
                grid-template-columns: 1fr;
                gap: 17px;
                padding: 17px;
            }

            .zo-panel-header {
                padding: 20px;
            }

            .zo-activity-empty {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>

    <div class="zo-servers-page">

        {{-- PAGE HEADER --}}
        <div class="zo-page-heading">
            <div>
                <div class="zo-breadcrumb">
                    Infrastructure / Servers
                </div>

                <h1>All Servers</h1>

                <p>
                    Manage your connected VPS infrastructure,
                    SSH access, and server operations.
                </p>
            </div>

            <a href="{{ route('panel.servers.add') }}"
               class="zo-btn zo-btn-primary">
                <i class="fas fa-plus"></i>
                Add New Server
            </a>
        </div>

        {{-- SERVER STATISTICS --}}
        <div class="zo-server-stats">

            <div class="zo-stat-card zo-stat-total">
                <div class="zo-stat-icon">
                    <i class="fas fa-server"></i>
                </div>

                <div class="zo-stat-number">
                    {{ $stats['total'] ?? 0 }}
                </div>

                <div class="zo-stat-label">
                    Total Servers
                </div>
            </div>

            <div class="zo-stat-card zo-stat-connected">
                <div class="zo-stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>

                <div class="zo-stat-number">
                    {{ $stats['connected'] ?? 0 }}
                </div>

                <div class="zo-stat-label">
                    Connected Servers
                </div>
            </div>

            <div class="zo-stat-card zo-stat-pending">
                <div class="zo-stat-icon">
                    <i class="fas fa-clock"></i>
                </div>

                <div class="zo-stat-number">
                    {{ $stats['pending'] ?? 0 }}
                </div>

                <div class="zo-stat-label">
                    Pending Setup
                </div>
            </div>

            <div class="zo-stat-card zo-stat-failed">
                <div class="zo-stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>

                <div class="zo-stat-number">
                    {{ $stats['failed'] ?? 0 }}
                </div>

                <div class="zo-stat-label">
                    Failed Checks
                </div>
            </div>

        </div>

        {{-- SERVER LIST --}}
        <section class="zo-server-panel">

            <div class="zo-panel-header">
                <div>
                    <h2>
                        <i class="fas fa-network-wired"></i>
                        Your Servers
                    </h2>

                    <p>
                        {{ $servers->count() }}
                        registered server(s)
                    </p>
                </div>

                <a href="{{ route('panel.servers.add') }}"
                   class="zo-btn zo-btn-outline">
                    <i class="fas fa-plus"></i>
                    New Server
                </a>
            </div>

            @if($servers->isEmpty())

                {{-- EMPTY STATE --}}
                <div class="zo-empty-state">
                    <div class="zo-empty-icon">
                        <i class="fas fa-server"></i>
                    </div>

                    <h3>No Servers Yet</h3>

                    <p>
                        Connect your first VPS to start managing
                        your infrastructure with ZeitOps.
                    </p>

                    <a href="{{ route('panel.servers.add') }}"
                       class="zo-btn zo-btn-primary">
                        <i class="fas fa-plus"></i>
                        Connect Your First Server
                    </a>
                </div>

            @else

                <div class="zo-server-list">

                    @foreach($servers as $server)

                        <article class="zo-server-item">

                            {{-- SERVER INFORMATION --}}
                            <div class="zo-server-identity">

                                <div class="zo-server-avatar">
                                    <i class="fas fa-server"></i>
                                </div>

                                <div class="zo-server-details">

                                    <h3>
                                        {{ $server['name'] }}
                                    </h3>

                                    <div class="zo-server-meta">
                                    <span>
                                        <i class="fas fa-network-wired"></i>
                                        {{ $server['ip'] ?? 'IP unavailable' }}
                                    </span>

                                        <span>
                                        <i class="fas fa-plug"></i>
                                        SSH {{ $server['port'] }}
                                    </span>

                                        <span>
                                        <i class="fas fa-user"></i>
                                        {{ $server['username'] }}
                                    </span>
                                    </div>

                                    <div class="zo-server-id">
                                        ID: {{ $server['id'] }}
                                    </div>

                                </div>
                            </div>

                            {{-- CONNECTION STATUS --}}
                            <div class="zo-server-status-section">

                            <span class="zo-status zo-status-{{ $server['status'] }}">

                                @if($server['status'] === 'connected')
                                    <i class="fas fa-check-circle"></i>
                                    Connected
                                @elseif($server['status'] === 'failed')
                                    <i class="fas fa-times-circle"></i>
                                    Failed
                                @else
                                    <i class="fas fa-clock"></i>
                                    Pending
                                @endif

                            </span>

                                <div class="zo-host-status">

                                    @if($server['host_verified'])
                                        <i class="fas fa-shield-alt zo-text-success"></i>
                                        Host Identity Confirmed
                                    @else
                                        <i class="fas fa-shield-alt zo-text-muted"></i>
                                        Host Identity Unverified
                                    @endif

                                </div>

                            </div>

                            {{-- LAST SSH SUCCESS --}}
                            <div class="zo-server-time">

                                <div class="zo-time-label">
                                    Last SSH Success
                                </div>

                                <div class="zo-time-value">
                                    {{ $server['last_connected_at'] ?? 'Never' }}
                                </div>

                                <div class="zo-time-label zo-time-secondary">
                                    Last Checked:
                                    {{ $server['last_checked_at'] ?? 'Never' }}
                                </div>

                            </div>

                            {{-- MANAGEMENT --}}
                            <div class="zo-server-actions">
                            <span class="zo-management-pending">
                                <i class="fas fa-tools"></i>
                                Management coming soon
                            </span>
                            </div>

                            {{-- LAST ERROR --}}
                            @if(
                                $server['status'] === 'failed' &&
                                !empty($server['last_error'])
                            )
                                <div class="zo-server-error">
                                    <i class="fas fa-exclamation-circle"></i>

                                    <span>
                                    {{ $server['last_error'] }}
                                </span>
                                </div>
                            @endif

                        </article>

                    @endforeach

                </div>

            @endif

        </section>

        {{-- SERVER ACTIVITY --}}
        <section class="zo-activity-panel">

            <div class="zo-panel-header">
                <div>
                    <h2>
                        <i class="fas fa-history"></i>
                        Server Activity
                    </h2>

                    <p>
                        Recent infrastructure events and operations
                    </p>
                </div>
            </div>

            <div class="zo-activity-empty">
                <i class="fas fa-clipboard-list"></i>

                <span>
                Server activity history will appear here
                when operation logging is enabled.
            </span>
            </div>

        </section>

    </div>

@endsection

