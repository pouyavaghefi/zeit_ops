
<div class="sidebar-menu">
    <ul>
        <li><a href="#" class="active"><i class="fas fa-home"></i> <span class="menu-text">Dashboard</span></a></li>

        <li class="menu-item has-submenu">
            <a href="#"><i></i> <span class="menu-text">Servers</span></a>
            <ul class="submenu">
                <li><a href="/servers/add">Add New Server</a></li>
                <li><a href="/servers/all">My Servers</a></li>
            </ul>
        </li>

        <li class="menu-item has-submenu">
            <a href="#"><i></i> <span class="menu-text">Sites</span></a>
            <ul class="submenu">
                <li><a href="#">Add New Site</a></li>
                <li><a href="#">All Sites</a></li>
            </ul>
        </li>

        <li class="menu-item has-submenu">
            <a href="#"><i></i> <span class="menu-text">Payments</span></a>
            <ul class="submenu">
                <li><a href="#">Paid Invoices</a></li>
                <li><a href="#">Online Wallet</a></li>
            </ul>
        </li>

        <li class="menu-item has-submenu">
            <a href="#"><i></i> <span class="menu-text">Tickets</span></a>
            <ul class="submenu">
                <li><a href="#">New Ticket</a></li>
                <li><a href="#">All Tickets</a></li>
            </ul>
        </li>

        <li><a href="#"><i class="fas fa-question-circle"></i> <span class="menu-text">Help & Support</span></a></li>
    </ul>
</div>

<div class="sidebar-footer">
    <a href="/logout" class="d-flex align-items-center text-white text-decoration-none">
        <i class="fas fa-sign-out-alt me-2"></i>
        <span class="menu-text">&nbsp;Logout</span>
    </a>
</div>

<!-- ✅ JavaScript at the end of body -->
<script>
    document.addEventListener("DOMContentLoaded", () => {
        // Define text → icon mapping
        const iconMap = {
            dashboard: "fa-home",
            servers: "fa-server",
            sites: "fa-globe",
            payments: "fa-credit-card",
            tickets: "fa-ticket-alt",
            "help & support": "fa-question-circle",
            logout: "fa-sign-out-alt"
        };

        // Loop through each menu item and assign icons
        document.querySelectorAll('.menu-item a, .sidebar-footer a').forEach(link => {
            const textEl = link.querySelector('.menu-text');
            const iconEl = link.querySelector('i');
            if (!textEl || !iconEl) return;

            const key = textEl.textContent.trim().toLowerCase();
            const iconClass = iconMap[key] || "fa-circle"; // default icon

            iconEl.className = `fas ${iconClass}`;
        });
    });
</script>
