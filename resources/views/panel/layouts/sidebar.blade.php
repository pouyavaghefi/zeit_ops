<nav id="sidebar">
    <div class="sidebar-header">
        <div class="logo"><a href="{{ env('APP_URL') }}"><img src="{{ asset('/img/favicon.png') }}" width="40" height="40"></div></a>
        <h3>HackAlone</h3>
    </div>

    <div class="sidebar-menu">
        <ul>
            <li><a href="#" class="active"><i class="fas fa-home"></i> <span class="menu-text">Dashboard</span></a></li>

            <li class="menu-item has-submenu">
                <a href="#"><i class="fas fa-server"></i> <span class="menu-text">Infrastructure</span></a>
                <ul class="submenu">
                    <li>
                        <a href="{{ url('/panel/servers/add') }}" class="{{ request()->path() === 'panel/servers/add' ? 'active' : '' }}">
                            <i class="fas fa-plus-circle"></i> Add New Server
                        </a>
                    </li>
                    <li><a href="#"><i class="fas fa-list"></i> All Servers</a></li>
                    <li><a href="#"><i class="fas fa-key"></i> SSH Keys & Access</a></li>
                    <li><a href="#"><i class="fas fa-shield-alt"></i> Firewall & Security</a></li>
                    <li><a href="#"><i class="fas fa-tachometer-alt"></i> System Monitor</a></li>
                    <li><a href="#"><i class="fas fa-hdd"></i> Backups & Restore</a></li>
                    <li><a href="#"><i class="fab fa-docker"></i> Docker Containers</a></li>
                    <li><a href="#"><i class="fas fa-cogs"></i> Services</a></li>
                </ul>
            </li>

            <li class="menu-item has-submenu">
                <a href="#"><i class="fas fa-globe"></i> <span class="menu-text">Websites</span></a>
                <ul class="submenu">
                    <li><a href="#"><i class="fas fa-plus-circle"></i> Add New Website</a></li>
                    <li><a href="#"><i class="fas fa-list"></i> All Websites</a></li>
                    <li><a href="#"><i class="fas fa-lock"></i> SSL & Domains</a></li>
                    <li><a href="#"><i class="fas fa-network-wired"></i> DNS Records</a></li>
                    <li><a href="#"><i class="fas fa-random"></i> Redirects & Subdomains</a></li>
                    <li><a href="#"><i class="fas fa-heartbeat"></i> Uptime Monitoring</a></li>
                </ul>
            </li>

            <li class="menu-item has-submenu">
                <a href="#"><i class="fas fa-cloud-upload-alt"></i> <span class="menu-text">Deployments</span></a>
                <ul class="submenu">
                    <li><a href="#"><i class="fas fa-plus-circle"></i> Add Deployment</a></li>
                    <li><a href="#"><i class="fas fa-list"></i> All Deployments</a></li>
                    <li><a href="#"><i class="fas fa-project-diagram"></i> CI/CD Pipelines</a></li>
                    <li><a href="#"><i class="fas fa-undo-alt"></i> Rollback Versions</a></li>
                    <li><a href="#"><i class="fas fa-layer-group"></i> Environment Manager</a></li>
                    <li><a href="#"><i class="fas fa-user-secret"></i> Env Variables / Secrets</a></li>
                </ul>
            </li>

            <li class="menu-item has-submenu">
                <a href="#"><i class="fas fa-database"></i> <span class="menu-text">Database & Storage</span></a>
                <ul class="submenu">
                    <li><a href="#"><i class="fas fa-plus-circle"></i> Add Database</a></li>
                    <li><a href="#"><i class="fas fa-list"></i> All Databases</a></li>
                    <li><a href="#"><i class="fas fa-clock"></i> Scheduled Backups</a></li>
                    <li><a href="#"><i class="fas fa-boxes"></i> Storage Usage</a></li>
                    <li><a href="#"><i class="fas fa-exchange-alt"></i> Migrations</a></li>
                </ul>
            </li>

            <li class="menu-item has-submenu">
                <a href="#"><i class="fas fa-wallet"></i> <span class="menu-text">Billing & Usage</span></a>
                <ul class="submenu">
                    <li><a href="#"><i class="fas fa-piggy-bank"></i> Wallet</a></li>
                    <li><a href="#"><i class="fas fa-credit-card"></i> My Payments</a></li>
                    <li><a href="#"><i class="fas fa-gem"></i> My Membership</a></li>
                    <li><a href="#"><i class="fas fa-chart-line"></i> Usage Reports</a></li>
                    <li><a href="#"><i class="fas fa-bell"></i> Budget Alerts</a></li>
                    <li><a href="#"><i class="fas fa-expand-arrows-alt"></i> Auto-Scaling</a></li>
                </ul>
            </li>

            <li class="menu-item has-submenu">
                <a href="#"><i class="fas fa-chart-bar"></i> <span class="menu-text">Analytics & Logs</span></a>
                <ul class="submenu">
                    <li><a href="#"><i class="fas fa-file-alt"></i> System Logs</a></li>
                    <li><a href="#"><i class="fas fa-laptop-code"></i> Application Logs</a></li>
                    <li><a href="#"><i class="fas fa-bug"></i> Error Tracking</a></li>
                    <li><a href="#"><i class="fas fa-chart-pie"></i> Performance Dashboard</a></li>
                    <li><a href="#"><i class="fas fa-search"></i> Log Search</a></li>
                </ul>
            </li>

            <li class="menu-item has-submenu">
                <a href="#"><i class="fas fa-user-shield"></i> <span class="menu-text">Users & Permissions</span></a>
                <ul class="submenu">
                    <li><a href="#"><i class="fas fa-user-plus"></i> Add User</a></li>
                    <li><a href="#"><i class="fas fa-users"></i> All Users</a></li>
                    <li><a href="#"><i class="fas fa-user-lock"></i> Roles & Permissions</a></li>
                    <li><a href="#"><i class="fas fa-clipboard-list"></i> Audit Logs</a></li>
                    <li><a href="#"><i class="fas fa-key"></i> SSO / Tokens</a></li>
                </ul>
            </li>

            <li class="menu-item has-submenu">
                <a href="#"><i class="fas fa-robot"></i> <span class="menu-text">Automation</span></a>
                <ul class="submenu">
                    <li><a href="#"><i class="fas fa-clock"></i> Add Cronjob</a></li>
                    <li><a href="#"><i class="fas fa-list"></i> All Cronjobs</a></li>
                    <li><a href="#"><i class="fas fa-terminal"></i> Script Runner</a></li>
                    <li><a href="#"><i class="fas fa-file-code"></i> Configuration Templates</a></li>
                    <li><a href="#"><i class="fas fa-plug"></i> Webhooks & API</a></li>
                </ul>
            </li>

            <li class="menu-item has-submenu">
                <a href="#"><i class="fas fa-ticket-alt"></i> <span class="menu-text">Tickets</span></a>
                <ul class="submenu">
                    <li><a href="#"><i class="fas fa-plus-circle"></i> Open Ticket</a></li>
                    <li><a href="#"><i class="fas fa-list"></i> All Tickets</a></li>
                    <li><a href="#"><i class="fas fa-check-circle"></i> Closed Tickets</a></li>
                </ul>
            </li>

            <li><a href="#"><i class="fas fa-book"></i> <span class="menu-text">Documentation</span></a></li>
        </ul>
    </div>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="d-flex align-items-center text-white text-decoration-none btn btn-link p-0 m-0">
                <i class="fas fa-sign-out-alt me-2"></i>
                <span class="menu-text">Logout</span>
            </button>
        </form>
    </div>

</nav>
