@extends('panel.layouts.master')

@section('wrapper')

    <div class="dashboard-content">

        {{-- Welcome --}}
        <div class="welcome-card">
            <h2>
                Welcome back, {{ auth()->user()->name }}!
            </h2>

            <p>
                Manage your servers, monitor SSH connections,
                and control your infrastructure with ZeitOps.
            </p>
        </div>

        {{-- Infrastructure Statistics --}}
        <div class="stats-cards">

            {{-- Total Servers --}}
            <div class="stat-card">
                <i class="fas fa-server"></i>

                <h3>{{ number_format($stats['total']) }}</h3>

                <p>Total Servers</p>

                <div class="progress-text">
                    <span>Registered infrastructure</span>
                    <span>{{ $stats['total'] }}</span>
                </div>

                <div class="progress">
                    <div
                        class="progress-bar bg-primary"
                        style="width: {{ $stats['total'] > 0 ? 100 : 0 }}%"
                    ></div>
                </div>
            </div>

            {{-- Successful Connections --}}
            <div class="stat-card">
                <i class="fas fa-check-circle"></i>

                <h3>{{ number_format($stats['connected']) }}</h3>

                <p>SSH Verified</p>

                <div class="progress-text">
                    <span>Last successful checks</span>
                    <span>
                    {{ $stats['total'] > 0
                        ? round($stats['connected'] / $stats['total'] * 100)
                        : 0 }}%
                </span>
                </div>

                <div class="progress">
                    <div
                        class="progress-bar bg-success"
                        style="width: {{ $stats['total'] > 0
                        ? round($stats['connected'] / $stats['total'] * 100)
                        : 0 }}%"
                    ></div>
                </div>
            </div>

            {{-- Pending Servers --}}
            <div class="stat-card">
                <i class="fas fa-clock"></i>

                <h3>{{ number_format($stats['pending']) }}</h3>

                <p>Pending Setup</p>

                <div class="progress-text">
                    <span>Awaiting completion</span>
                    <span>{{ $stats['pending'] }}</span>
                </div>

                <div class="progress">
                    <div
                        class="progress-bar bg-warning"
                        style="width: {{ $stats['total'] > 0
                        ? round($stats['pending'] / $stats['total'] * 100)
                        : 0 }}%"
                    ></div>
                </div>
            </div>

            {{-- Failed Servers --}}
            <div class="stat-card">
                <i class="fas fa-exclamation-triangle"></i>

                <h3>{{ number_format($stats['failed']) }}</h3>

                <p>Failed Checks</p>

                <div class="progress-text">
                    <span>Recorded failures</span>
                    <span>{{ $stats['failed'] }}</span>
                </div>

                <div class="progress">
                    <div
                        class="progress-bar bg-danger"
                        style="width: {{ $stats['total'] > 0
                        ? round($stats['failed'] / $stats['total'] * 100)
                        : 0 }}%"
                    ></div>
                </div>
            </div>

        </div>

        {{-- My Servers --}}
        <div class="card border-0 shadow-sm mt-4"
             style="border-radius: 15px;">

            <div class="card-header bg-white border-0 p-4">
                <div class="d-flex justify-content-between
                        align-items-center flex-wrap gap-3">

                    <div>
                        <h4 class="mb-1 fw-bold">
                            My Servers
                        </h4>

                        <p class="text-muted mb-0">
                            Your registered ZeitOps infrastructure
                        </p>
                    </div>

                    <a
                        href="{{ url('/panel/servers/add') }}"
                        class="btn btn-primary"
                    >
                        <i class="fas fa-plus me-2"></i>
                        Add Server
                    </a>

                </div>
            </div>

            <div class="card-body p-0">

                @if($servers->isEmpty())

                    <div class="text-center py-5 px-3">

                        <i class="fas fa-server fa-3x text-muted mb-3"></i>

                        <h5>No Servers Yet</h5>

                        <p class="text-muted">
                            Connect your first VPS to start
                            managing your infrastructure.
                        </p>

                        <a
                            href="{{ url('/panel/servers/add') }}"
                            class="btn btn-primary"
                        >
                            Add Your First Server
                        </a>

                    </div>

                @else

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">

                            <thead class="table-light">
                            <tr>
                                <th class="ps-4">Server</th>
                                <th>SSH Port</th>
                                <th>Status</th>
                                <th>Host Identity</th>
                                <th>Last SSH Success</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                            </thead>

                            <tbody>

                            @foreach($servers as $server)

                                <tr>

                                    <td class="ps-4">
                                        <div class="fw-semibold">
                                            {{ $server['ip'] }}
                                        </div>

                                        <small class="text-muted">
                                            {{ $server['id'] }}
                                        </small>
                                    </td>

                                    <td>
                                        {{ $server['port'] }}
                                    </td>

                                    <td>
                                        @if($server['status'] === 'connected')
                                            <span class="badge bg-success">
                                                SSH Verified
                                            </span>

                                        @elseif($server['status'] === 'failed')
                                            <span class="badge bg-danger">
                                                Failed
                                            </span>

                                        @else
                                            <span class="badge bg-warning text-dark">
                                                Pending
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($server['fingerprint_verified'])
                                            <span class="text-success">
                                                <i class="fas fa-shield-alt me-1"></i>
                                                Confirmed
                                            </span>
                                        @else
                                            <span class="text-muted">
                                                Not verified
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($server['last_connected_at'])
                                            {{ \Illuminate\Support\Carbon::parse(
                                                $server['last_connected_at']
                                            )->format('d M Y, H:i') }}
                                        @else
                                            <span class="text-muted">Never</span>
                                        @endif
                                    </td>

                                    <td class="text-end pe-4">
                                        <span class="text-muted small">
                                            Management coming soon
                                        </span>
                                    </td>

                                </tr>

                            @endforeach

                            </tbody>

                        </table>

                    </div>

                @endif

            </div>

        </div>

    </div>

@endsection
