<div class="topbar d-flex align-items-center justify-content-between px-3 py-2 bg-light shadow-sm">
    <!-- Sidebar Toggle -->
    <button
        type="button"
        id="sidebar-toggle"
        class="btn border-0"
        aria-label="Toggle sidebar"
        aria-controls="sidebar"
        aria-expanded="false"
    >
        <i class="fas fa-bars"></i>
    </button>

    <!-- Right Side: Notifications, Messages, Profile -->
    <div class="d-flex align-items-center gap-3">

        <!-- Notifications -->
        <div class="dropdown">
            <a href="#" class="position-relative text-dark" data-bs-toggle="dropdown" id="notificationDropdown">
                <i class="fas fa-bell fs-5"></i>
                @php $unreadCount = $notifications->whereNull('read_at')->count(); @endphp
                @if($unreadCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    {{ $unreadCount }}
                </span>
                @endif
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow notification-dropdown">
                <li class="px-3 py-2 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="mb-0">Notifications</h6>
                    <a href="{{ route('panel.notifications') }}">View All</a>
                </li>
                <li>
                    <div class="notification-list p-2">
                        @forelse($notifications->whereNull('read_at') as $notification)
                            <div class="notification-item unread d-flex align-items-start mb-2">
                                <div class="notification-icon me-2 text-info">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $notification->activity }}</h6>
                                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted">No new notifications.</div>
                        @endforelse
                    </div>
                </li>
            </ul>
        </div>

{{--        <!-- Messages -->--}}
{{--        <div class="dropdown">--}}
{{--            <a href="#" class="position-relative text-dark" data-bs-toggle="dropdown">--}}
{{--                <i class="fas fa-envelope fs-5"></i>--}}
{{--                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">1</span>--}}
{{--            </a>--}}
{{--            <ul class="dropdown-menu dropdown-menu-end shadow messages-dropdown">--}}
{{--                <li class="px-3 py-2 d-flex justify-content-between align-items-center border-bottom">--}}
{{--                    <h6 class="mb-0">Messages</h6>--}}
{{--                    <a href="#">Mark all as read</a>--}}
{{--                </li>--}}
{{--                <li>--}}
{{--                    <div class="messages-list p-2">--}}
{{--                        <div class="message-item unread d-flex align-items-start mb-2">--}}
{{--                            <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="User" class="rounded-circle me-2" width="40">--}}
{{--                            <div>--}}
{{--                                <h6 class="mb-1">--}}
{{--                                    <span class="fw-bold">Sarah Johnson</span>--}}
{{--                                    <small class="text-muted ms-2">12:45 PM</small>--}}
{{--                                </h6>--}}
{{--                                <p class="mb-0 small text-truncate" style="max-width:200px;">Hi there! I have a question about the new product features...</p>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </li>--}}
{{--                <li class="px-3 py-2 border-top"><a href="#">View All Messages</a></li>--}}
{{--            </ul>--}}
{{--        </div>--}}


        {{-- ZeitOps User Profile --}}



        @auth
            @php
                $user = auth()->user();

                $initials = collect(
                    preg_split('/\s+/', trim($user->name ?? 'User'))
                )
                    ->filter()
                    ->take(2)
                    ->map(fn ($part) => strtoupper(mb_substr($part, 0, 1)))
                    ->implode('') ?: 'U';

                /*
                |--------------------------------------------------------------------------
                | Workspace Data (Frontend Only)
                |--------------------------------------------------------------------------
                */

                $personalWorkspace = [
                    'id' => 'personal',
                    'name' => 'Personal Workspace',
                    'type' => 'personal',
                    'description' => 'Your private infrastructure',
                    'role' => 'Owner',
                ];

                // No teams created yet
                $teams = null;

                /*
                |--------------------------------------------------------------------------
                | Team Workspace Quota
                |--------------------------------------------------------------------------
                */

                $maxTeams = 5;

                $usedTeams = count($teams ?? []);

                $remainingTeams = max(0, $maxTeams - $usedTeams);

                $usagePercentage = $maxTeams > 0
                    ? min(100, ($usedTeams / $maxTeams) * 100)
                    : 0;

                $totalWorkspaces = 1 + $usedTeams;
            @endphp

            <div class="dropdown zeitops-profile">

                {{-- =====================================
                     PROFILE TOGGLE
                ===================================== --}}

                <button
                    type="button"
                    class="zp-toggle"
                    data-bs-toggle="dropdown"
                    data-bs-auto-close="outside"
                    aria-expanded="false"
                    aria-label="Open account and workspace menu"
                >
            <span class="zp-avatar">
                {{ $initials }}
            </span>

                    <i class="fas fa-chevron-down zp-chevron"></i>
                </button>

                {{-- =====================================
                     PROFILE DROPDOWN
                ===================================== --}}

                <div class="dropdown-menu dropdown-menu-end zp-dropdown">

                    {{-- =====================================
                         ACCOUNT HEADER
                    ===================================== --}}

                    <div class="zp-header">

                        <div class="zp-header-avatar">
                            {{ $initials }}
                        </div>

                        <div class="zp-header-info">

                            <div class="zp-header-name">
                                {{ $user->name }}
                            </div>

                            <div class="zp-header-email">
                                {{ $user->email }}
                            </div>

                            <span class="zp-verified {{ $user->email_verified_at ? '' : 'zp-unverified' }}">

                        <i class="fas {{ $user->email_verified_at
                            ? 'fa-check-circle'
                            : 'fa-exclamation-circle' }}">
                        </i>

                        {{ $user->email_verified_at
                            ? 'Verified Account'
                            : 'Email Not Verified' }}

                    </span>

                        </div>

                    </div>

                    <div class="zp-divider"></div>

                    {{-- =====================================
                         WORKSPACE SWITCHER
                    ===================================== --}}

                    <div class="zp-workspaces">

                        <div class="zp-section-heading">

                            <span>WORKSPACES</span>

                            <span class="zp-workspace-count">
                        {{ $totalWorkspaces }}
                    </span>

                        </div>

                        {{-- PERSONAL WORKSPACE --}}

                        <div class="zp-workspace-group-label">
                            PERSONAL
                        </div>

                        <button
                            type="button"
                            class="zp-workspace active"
                            data-workspace-id="{{ $personalWorkspace['id'] }}"
                            data-workspace-name="{{ $personalWorkspace['name'] }}"
                            data-workspace-type="personal"
                            aria-pressed="true"
                        >

                    <span class="zp-workspace-avatar zp-personal">
                        <i class="fas fa-user"></i>
                    </span>

                            <span class="zp-workspace-details">

                        <strong>
                            {{ $personalWorkspace['name'] }}
                        </strong>

                        <small>
                            {{ $personalWorkspace['description'] }}
                        </small>

                    </span>

                            <span class="zp-workspace-check">
                        <i class="fas fa-check-circle"></i>
                    </span>

                        </button>

                        {{-- =====================================
                             TEAM WORKSPACES
                        ===================================== --}}

                        @if(!empty($teams))

                            <div class="zp-workspace-group-label zp-team-label">
                                TEAMS
                            </div>

                            @foreach($teams as $team)

                                <button
                                    type="button"
                                    class="zp-workspace"
                                    data-workspace-id="{{ $team['id'] }}"
                                    data-workspace-name="{{ $team['name'] }}"
                                    data-workspace-type="team"
                                    aria-pressed="false"
                                >

                            <span class="zp-workspace-avatar zp-team">
                                <i class="fas fa-users"></i>
                            </span>

                                    <span class="zp-workspace-details">

                                <strong>
                                    {{ $team['name'] }}
                                </strong>

                                <small>

                                    {{ $team['members'] }} members

                                    <span class="zp-dot">·</span>

                                    {{ $team['role'] }}

                                </small>

                            </span>

                                    <span class="zp-workspace-check">
                                <i class="fas fa-check-circle"></i>
                            </span>

                                </button>

                            @endforeach

                        @endif

                    </div>

                    <div class="zp-divider"></div>

                    {{-- =====================================
                         ACCOUNT NAVIGATION
                    ===================================== --}}

                    <div class="zp-menu">

                        {{-- Dashboard --}}

                        <a
                            href="{{ url('/panel/dashboard') }}"
                            class="zp-menu-link"
                        >
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>

                        {{-- Profile Settings --}}

                        <a
                            href="{{ url('/panel/profile-settings') }}"
                            class="zp-menu-link"
                        >
                            <i class="fas fa-user-cog"></i>
                            <span>Profile Settings</span>
                        </a>

                    </div>

                    <div class="zp-divider"></div>

                    {{-- =====================================
                         TEAM WORKSPACE QUOTA
                    ===================================== --}}

                    <div class="zp-team-quota">

                        {{-- Header --}}

                        <div class="zp-team-quota-header">

                            <div class="zp-team-quota-icon">
                                <i class="fas fa-users"></i>
                            </div>

                            <div class="zp-team-quota-title">
                                <strong>Team Workspace Quota</strong>
                            </div>

                            <span class="zp-team-quota-count">
                        {{ $usedTeams }} of {{ $maxTeams }}
                    </span>

                        </div>

                        {{-- Progress Bar --}}

                        <div
                            class="zp-team-quota-progress"
                            role="progressbar"
                            aria-label="Team workspace usage"
                            aria-valuenow="{{ $usedTeams }}"
                            aria-valuemin="0"
                            aria-valuemax="{{ $maxTeams }}"
                        >

                            <div
                                class="zp-team-quota-progress-fill"
                                style="width: {{ $usagePercentage }}%"
                            ></div>

                        </div>

                        {{-- Quota Details --}}

                        <div class="zp-team-quota-footer">

                    <span class="zp-team-quota-description">

                        You have created
                        {{ $usedTeams }} of {{ $maxTeams }}
                        available team workspaces.

                    </span>

                            <span class="zp-team-quota-remaining">
                        {{ $remainingTeams }} remaining
                    </span>

                        </div>

                    </div>

                    {{-- =====================================
                         WORKSPACE ACTIONS
                    ===================================== --}}

                    <div class="zp-team-actions">

                        {{-- Manage Workspaces --}}

                        <button
                            type="button"
                            class="zp-manage-workspaces"
                            data-zp-placeholder="Manage Workspaces"
                        >

                            <i class="fas fa-cog"></i>

                            <span>
                        Manage Workspaces
                    </span>

                        </button>

                        {{-- Create New Workspace --}}

                        <button
                            type="button"
                            class="zp-create-workspace"
                            data-zp-placeholder="Create New Workspace"
                            @disabled($remainingTeams <= 0)
                        >

                            <i class="fas fa-plus"></i>

                            <span>
                        Create New Workspace
                    </span>

                        </button>

                    </div>

                    <div class="zp-divider"></div>

                    {{-- =====================================
                         LOGOUT
                    ===================================== --}}

                    <div class="zp-footer">

                        <form
                            method="POST"
                            action="{{ route('logout') }}"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="zp-logout"
                            >

                                <i class="fas fa-sign-out-alt"></i>

                                <span>
                            Sign Out
                        </span>

                            </button>

                        </form>

                    </div>

                </div>

            </div>
        @endauth
    </div>
</div>

<script>
    document.getElementById('notificationDropdown')?.addEventListener('click', function() {
        fetch("{{ route('panel.notifications.markRead') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success'){
                    this.querySelector('.badge')?.remove();
                    document.querySelectorAll('.notification-item.unread').forEach(el => el.classList.remove('unread'));
                }
            });
    });
</script>
