<div class="topbar d-flex align-items-center justify-content-between px-3 py-2 bg-light shadow-sm">
    <!-- Sidebar Toggle -->
    <button class="btn btn-light" id="sidebarToggle">
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

        <!-- Messages -->
        <div class="dropdown">
            <a href="#" class="position-relative text-dark" data-bs-toggle="dropdown">
                <i class="fas fa-envelope fs-5"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">1</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow messages-dropdown">
                <li class="px-3 py-2 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="mb-0">Messages</h6>
                    <a href="#">Mark all as read</a>
                </li>
                <li>
                    <div class="messages-list p-2">
                        <div class="message-item unread d-flex align-items-start mb-2">
                            <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="User" class="rounded-circle me-2" width="40">
                            <div>
                                <h6 class="mb-1">
                                    <span class="fw-bold">Sarah Johnson</span>
                                    <small class="text-muted ms-2">12:45 PM</small>
                                </h6>
                                <p class="mb-0 small text-truncate" style="max-width:200px;">Hi there! I have a question about the new product features...</p>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="px-3 py-2 border-top"><a href="#">View All Messages</a></li>
            </ul>
        </div>

        <!-- User Profile -->
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center" data-bs-toggle="dropdown">
                <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="User" class="rounded-circle" width="40">
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow profile-dropdown">
                <li class="dropdown-header d-flex align-items-center p-3 border-bottom">
                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="User" class="rounded-circle me-2" width="50">
                    <div>
                        <h6 class="mb-0">John Doe</h6>
                        <small class="text-muted">Administrator</small>
                    </div>
                </li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> My Profile</a></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Account Settings</a></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-envelope me-2"></i> Messages <span class="badge bg-primary ms-2">1</span></a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-question-circle me-2"></i> Help Center</a></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
            </ul>
        </div>
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
