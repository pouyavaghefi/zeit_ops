<div class="user-actions">
    <div class="notification dropdown">
        <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-bell"></i>
            <span class="badge">1</span>
        </a>
        <ul class="dropdown-menu notification-dropdown">
            <li class="notification-header">
                <h5>Notifications</h5>
                <a href="#">Mark all as read</a>
            </li>
            <li>
                <div class="notification-list">
                    <div class="notification-item unread">
                        <div class="notification-icon info">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="notification-content">
                            <h6>Welcome {{ auth()->user()->name }}</h6>
                            <p>Welcome to our user dashboard, click to see some quick guidelines...</p>
                            <div class="notification-time">10 minutes ago</div>
                        </div>
                    </div>
                </div>
            </li>
            <li class="notification-footer">
                <a href="#">View All Notifications</a>
            </li>
        </ul>
    </div>

    <div class="messages dropdown">
        <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-envelope"></i>
            <span class="badge"></span>
        </a>
        <ul class="dropdown-menu messages-dropdown">
            <li class="messages-header">
                <h5>Messages</h5>
                <a href="#">Mark all as read</a>
            </li>
            <li>
                <div class="messages-list">
                    <div class="message-item unread">
                        <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="User" class="message-avatar">
                        <div class="message-content">
                            <h6>
                                <span class="message-sender">Sarah Johnson</span>
                                <span class="message-time">12:45 PM</span>
                            </h6>
                            <p>Hi there! I have a question about the new product features...</p>
                        </div>
                    </div>
                </div>
            </li>
            <li class="messages-footer">
                <a href="#">View All Messages</a>
            </li>
        </ul>
    </div>

    <div class="user-profile dropdown">
        <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="User">
        </a>
        <ul class="dropdown-menu profile-dropdown">
            <li class="dropdown-header">
                <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="User">
                <div>
                    <h5>{{ auth()->user()->fullName() }}</h5>
                    <p>Client</p>
                </div>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="/my-profile"><i class="fas fa-user"></i> My Profile</a></li>
            <li><a class="dropdown-item" href="/account-setings"><i class="fas fa-cog"></i> Account Settings</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="/logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
</div>
