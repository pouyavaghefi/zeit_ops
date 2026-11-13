    // Toggle sidebar
    document.getElementById('sidebarToggle').addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('collapsed');
});

    // Add active class to clicked menu items
    const menuItems = document.querySelectorAll('.sidebar-menu a');
    menuItems.forEach(item => {
    item.addEventListener('click', function() {
        menuItems.forEach(i => i.classList.remove('active'));
        this.classList.add('active');
    });
});

    // Submenu toggle functionality
    const menuItemsWithSubmenu = document.querySelectorAll('.menu-item.has-submenu > a');
    menuItemsWithSubmenu.forEach(item => {
    item.addEventListener('click', function(e) {
        e.preventDefault();
        const parent = this.parentElement;
        const submenu = parent.querySelector('.submenu');

        // Close other open submenus
        document.querySelectorAll('.submenu.show').forEach(openSubmenu => {
            if (openSubmenu !== submenu) {
                openSubmenu.classList.remove('show');
                openSubmenu.parentElement.classList.remove('active');
            }
        });

        // Toggle current submenu
        submenu.classList.toggle('show');
        parent.classList.toggle('active');
    });
});

    // Simulate loading animation for stats cards
    window.addEventListener('load', function() {
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
    setTimeout(() => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'all 0.5s ease';

    setTimeout(() => {
    card.style.opacity = '1';
    card.style.transform = 'translateY(0)';
}, 100);
}, index * 100);
});
});

    // Add hover effects to cards
    const cards = document.querySelectorAll('.stat-card, .chart-container, .recent-activities');
    cards.forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-5px)';
    });

    card.addEventListener('mouseleave', function() {
    this.style.transform = 'translateY(0)';
});
});

    // Horizontal navbar active state
    const horizontalNavItems = document.querySelectorAll('.horizontal-nav a');
    horizontalNavItems.forEach(item => {
    item.addEventListener('click', function() {
        horizontalNavItems.forEach(i => i.classList.remove('active'));
        this.classList.add('active');
    });
});

    // Mark notifications as read
    document.querySelectorAll('.notification-item').forEach(item => {
    item.addEventListener('click', function() {
        this.classList.remove('unread');
        updateNotificationBadge();
    });
});

    // Mark messages as read
    document.querySelectorAll('.message-item').forEach(item => {
    item.addEventListener('click', function() {
        this.classList.remove('unread');
        updateMessageBadge();
    });
});

    // Update notification badge count
    function updateNotificationBadge() {
    const unreadCount = document.querySelectorAll('.notification-item.unread').length;
    const badge = document.querySelector('.notification .badge');
    badge.textContent = unreadCount;
    if (unreadCount === 0) {
    badge.style.display = 'none';
} else {
    badge.style.display = 'flex';
}
}

    // Update message badge count
    function updateMessageBadge() {
    const unreadCount = document.querySelectorAll('.message-item.unread').length;
    const badge = document.querySelector('.messages .badge');
    badge.textContent = unreadCount;
    if (unreadCount === 0) {
    badge.style.display = 'none';
} else {
    badge.style.display = 'flex';
}
}

    // Mark all notifications as read
    document.querySelector('.notification-header a').addEventListener('click', function(e) {
    e.preventDefault();
    document.querySelectorAll('.notification-item.unread').forEach(item => {
    item.classList.remove('unread');
});
    updateNotificationBadge();
});

    // Mark all messages as read
    document.querySelector('.messages-header a').addEventListener('click', function(e) {
    e.preventDefault();
    document.querySelectorAll('.message-item.unread').forEach(item => {
    item.classList.remove('unread');
});
    updateMessageBadge();
});
