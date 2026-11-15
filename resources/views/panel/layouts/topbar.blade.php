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
                    ->implode('');

                $initials = $initials ?: 'U';
            @endphp

            <div class="dropdown zeitops-profile">

                {{-- Profile Toggle --}}
                <button
                    type="button"
                    class="profile-toggle"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="Open account menu"
                >
            <span class="profile-avatar">
                {{ $initials }}
            </span>
                </button>

                {{-- Dropdown --}}
                <ul class="dropdown-menu dropdown-menu-end shadow profile-dropdown">

                    {{-- User Information --}}
                    <li>
                        <div class="profile-dropdown-header">
                    <span class="profile-avatar profile-avatar-lg">
                        {{ $initials }}
                    </span>

                            <div class="profile-user-info">
                                <h6 class="mb-1">
                                    {{ $user->name }}
                                </h6>

                                <small class="text-muted">
                                    {{ $user->email }}
                                </small>
                            </div>
                        </div>
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    {{-- Dashboard --}}
                    <li>
                        <a
                            class="dropdown-item"
                            href="{{ url('/panel/dashboard') }}"
                        >
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard
                        </a>
                    </li>

                    {{-- My Servers --}}
                    <li>
                        <a
                            class="dropdown-item"
                            href="{{ url('/panel/dashboard') }}"
                        >
                            <i class="fas fa-server"></i>
                            My Servers
                        </a>
                    </li>

                    {{-- Account information --}}
                    <li>
                        <div class="dropdown-item-text profile-account-info">
                            <i class="fas fa-check-circle text-success"></i>
                            <span>
                        {{ $user->email_verified_at
                            ? 'Verified Account'
                            : 'Email Not Verified' }}
                    </span>
                        </div>
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    {{-- Logout --}}
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button
                                type="submit"
                                class="dropdown-item profile-logout"
                            >
                                <i class="fas fa-sign-out-alt"></i>
                                Sign Out
                            </button>
                        </form>
                    </li>

                </ul>
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
