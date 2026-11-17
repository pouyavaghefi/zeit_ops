
@php
    /*
    |--------------------------------------------------------------------------
    | ZeitOps Sidebar Active Routes
    |--------------------------------------------------------------------------
    */

    $dashboardActive = request()->is('panel/dashboard');

    $infrastructureActive = request()->is('panel/servers*');

    $addServerActive = request()->is('panel/servers/add');

    $websitesActive = request()->is('panel/websites*');

    $deploymentsActive = request()->is('panel/deployments*');

    $databasesActive = request()->is('panel/databases*')
        || request()->is('panel/storage*');

    $billingActive = request()->is('panel/billing*');

    $analyticsActive = request()->is('panel/analytics*')
        || request()->is('panel/logs*');

    $usersActive = request()->is('panel/users*')
        || request()->is('panel/roles*');

    $automationActive = request()->is('panel/automation*');

    $ticketsActive = request()->is('panel/tickets*');

    $documentationActive = request()->is('panel/documentation*');
@endphp

<nav id="sidebar">

    {{-- =====================================
         ZEITOPS BRAND
    ===================================== --}}
    <div class="sidebar-header">

        <a
            href="{{ url('/panel/dashboard') }}"
            class="sidebar-brand"
            aria-label="ZeitOps Dashboard"
        >
            <img
                src="{{ asset('img/sidebar.png') }}"
                alt="ZeitOps"
                class="sidebar-logo"
            >
        </a>

    </div>

    {{-- =====================================
         SIDEBAR MENU
    ===================================== --}}
    <div class="sidebar-menu">

        <ul>

            {{-- DASHBOARD --}}
            <li>
                <a
                    href="{{ url('/panel/dashboard') }}"
                    class="{{ $dashboardActive ? 'active' : '' }}"
                    @if($dashboardActive) aria-current="page" @endif
                >
                    <i class="fas fa-home"></i>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>

            {{-- =====================================
                 INFRASTRUCTURE
            ===================================== --}}
            <li class="menu-item has-submenu {{ $infrastructureActive ? 'submenu-open' : '' }}">

                <a
                    href="#"
                    aria-expanded="{{ $infrastructureActive ? 'true' : 'false' }}"
                >
                    <i class="fas fa-server"></i>
                    <span class="menu-text">Infrastructure</span>
                </a>

                <ul
                    class="submenu"
                    @if(!$infrastructureActive) hidden @endif
                >

                    <li>
                        <a
                            href="{{ url('/panel/servers/add') }}"
                            class="{{ $addServerActive ? 'active' : '' }}"
                            @if($addServerActive) aria-current="page" @endif
                        >
                            <i class="fas fa-plus-circle"></i>
                            Add New Server
                        </a>
                    </li>

                    <li>
                        <a href="/panel/servers">
                            <i class="fas fa-list"></i>
                            All Servers
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="fas fa-key"></i>
                            SSH Keys & Access
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="fas fa-shield-alt"></i>
                            Firewall & Security
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="fas fa-tachometer-alt"></i>
                            System Monitor
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="fas fa-hdd"></i>
                            Backups & Restore
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="fab fa-docker"></i>
                            Docker Containers
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="fas fa-cogs"></i>
                            Services
                        </a>
                    </li>

                </ul>
            </li>

            {{-- =====================================
                 WEBSITES
            ===================================== --}}
            <li class="menu-item has-submenu {{ $websitesActive ? 'submenu-open' : '' }}">

                <a
                    href="#"
                    aria-expanded="{{ $websitesActive ? 'true' : 'false' }}"
                >
                    <i class="fas fa-globe"></i>
                    <span class="menu-text">Websites</span>
                </a>

                <ul class="submenu" @if(!$websitesActive) hidden @endif>

                    <li>
                        <a href="#">
                            <i class="fas fa-plus-circle"></i>
                            Add New Website
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="fas fa-list"></i>
                            All Websites
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="fas fa-lock"></i>
                            SSL & Domains
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="fas fa-network-wired"></i>
                            DNS Records
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="fas fa-random"></i>
                            Redirects & Subdomains
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="fas fa-heartbeat"></i>
                            Uptime Monitoring
                        </a>
                    </li>

                </ul>
            </li>

            {{-- =====================================
                 DEPLOYMENTS
            ===================================== --}}
            <li class="menu-item has-submenu {{ $deploymentsActive ? 'submenu-open' : '' }}">

                <a
                    href="#"
                    aria-expanded="{{ $deploymentsActive ? 'true' : 'false' }}"
                >
                    <i class="fas fa-cloud-upload-alt"></i>
                    <span class="menu-text">Deployments</span>
                </a>

                <ul class="submenu" @if(!$deploymentsActive) hidden @endif>

                    <li>
                        <a href="#">
                            <i class="fas fa-plus-circle"></i>
                            Add Deployment
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="fas fa-list"></i>
                            All Deployments
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="fas fa-project-diagram"></i>
                            CI/CD Pipelines
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="fas fa-undo-alt"></i>
                            Rollback Versions
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="fas fa-layer-group"></i>
                            Environment Manager
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="fas fa-user-secret"></i>
                            Env Variables / Secrets
                        </a>
                    </li>

                </ul>
            </li>

            {{-- =====================================
                 DATABASE & STORAGE
            ===================================== --}}
            <li class="menu-item has-submenu {{ $databasesActive ? 'submenu-open' : '' }}">

                <a
                    href="#"
                    aria-expanded="{{ $databasesActive ? 'true' : 'false' }}"
                >
                    <i class="fas fa-database"></i>
                    <span class="menu-text">Database & Storage</span>
                </a>

                <ul class="submenu" @if(!$databasesActive) hidden @endif>

                    <li>
                        <a href="#">
                            <i class="fas fa-plus-circle"></i>
                            Add Database
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="fas fa-list"></i>
                            All Databases
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="fas fa-clock"></i>
                            Scheduled Backups
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="fas fa-boxes"></i>
                            Storage Usage
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="fas fa-exchange-alt"></i>
                            Migrations
                        </a>
                    </li>

                </ul>
            </li>

            {{-- =====================================
                 BILLING & USAGE
            ===================================== --}}
            <li class="menu-item has-submenu {{ $billingActive ? 'submenu-open' : '' }}">

                <a
                    href="#"
                    aria-expanded="{{ $billingActive ? 'true' : 'false' }}"
                >
                    <i class="fas fa-wallet"></i>
                    <span class="menu-text">Billing & Usage</span>
                </a>

                <ul class="submenu" @if(!$billingActive) hidden @endif>

                    <li>
                        <a href="#">
                            <i class="fas fa-piggy-bank"></i>
                            Wallet
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="fas fa-credit-card"></i>
                            My Payments
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="fas fa-gem"></i>
                            My Membership
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="fas fa-chart-line"></i>
                            Usage Reports
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="fas fa-bell"></i>
                            Budget Alerts
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="fas fa-expand-arrows-alt"></i>
                            Auto-Scaling
                        </a>
                    </li>

                </ul>
            </li>

            {{-- =====================================
                 ANALYTICS & LOGS
            ===================================== --}}
            <li class="menu-item has-submenu {{ $analyticsActive ? 'submenu-open' : '' }}">

                <a
                    href="#"
                    aria-expanded="{{ $analyticsActive ? 'true' : 'false' }}"
                >
                    <i class="fas fa-chart-bar"></i>
                    <span class="menu-text">Analytics & Logs</span>
                </a>

                <ul class="submenu" @if(!$analyticsActive) hidden @endif>

                    <li><a href="#"><i class="fas fa-file-alt"></i> System Logs</a></li>
                    <li><a href="#"><i class="fas fa-laptop-code"></i> Application Logs</a></li>
                    <li><a href="#"><i class="fas fa-bug"></i> Error Tracking</a></li>
                    <li><a href="#"><i class="fas fa-chart-pie"></i> Performance Dashboard</a></li>
                    <li><a href="#"><i class="fas fa-search"></i> Log Search</a></li>

                </ul>
            </li>

            {{-- =====================================
                 USERS & PERMISSIONS
            ===================================== --}}
            <li class="menu-item has-submenu {{ $usersActive ? 'submenu-open' : '' }}">

                <a
                    href="#"
                    aria-expanded="{{ $usersActive ? 'true' : 'false' }}"
                >
                    <i class="fas fa-user-shield"></i>
                    <span class="menu-text">Users & Permissions</span>
                </a>

                <ul class="submenu" @if(!$usersActive) hidden @endif>

                    <li><a href="#"><i class="fas fa-user-plus"></i> Add User</a></li>
                    <li><a href="#"><i class="fas fa-users"></i> All Users</a></li>
                    <li><a href="#"><i class="fas fa-user-lock"></i> Roles & Permissions</a></li>
                    <li><a href="#"><i class="fas fa-clipboard-list"></i> Audit Logs</a></li>
                    <li><a href="#"><i class="fas fa-key"></i> SSO / Tokens</a></li>

                </ul>
            </li>

            {{-- =====================================
                 AUTOMATION
            ===================================== --}}
            <li class="menu-item has-submenu {{ $automationActive ? 'submenu-open' : '' }}">

                <a
                    href="#"
                    aria-expanded="{{ $automationActive ? 'true' : 'false' }}"
                >
                    <i class="fas fa-robot"></i>
                    <span class="menu-text">Automation</span>
                </a>

                <ul class="submenu" @if(!$automationActive) hidden @endif>

                    <li><a href="#"><i class="fas fa-clock"></i> Add Cronjob</a></li>
                    <li><a href="#"><i class="fas fa-list"></i> All Cronjobs</a></li>
                    <li><a href="#"><i class="fas fa-terminal"></i> Script Runner</a></li>
                    <li><a href="#"><i class="fas fa-file-code"></i> Configuration Templates</a></li>
                    <li><a href="#"><i class="fas fa-plug"></i> Webhooks & API</a></li>

                </ul>
            </li>

            {{-- =====================================
                 TICKETS
            ===================================== --}}
            <li class="menu-item has-submenu {{ $ticketsActive ? 'submenu-open' : '' }}">

                <a
                    href="#"
                    aria-expanded="{{ $ticketsActive ? 'true' : 'false' }}"
                >
                    <i class="fas fa-ticket-alt"></i>
                    <span class="menu-text">Tickets</span>
                </a>

                <ul class="submenu" @if(!$ticketsActive) hidden @endif>

                    <li><a href="#"><i class="fas fa-plus-circle"></i> Open Ticket</a></li>
                    <li><a href="#"><i class="fas fa-list"></i> All Tickets</a></li>
                    <li><a href="#"><i class="fas fa-check-circle"></i> Closed Tickets</a></li>

                </ul>
            </li>

            {{-- =====================================
                 DOCUMENTATION
            ===================================== --}}
            <li>
                <a
                    href="#"
                    class="{{ $documentationActive ? 'active' : '' }}"
                >
                    <i class="fas fa-book"></i>
                    <span class="menu-text">Documentation</span>
                </a>
            </li>

        </ul>

    </div>

    {{-- =====================================
         FOOTER / LOGOUT
    ===================================== --}}
    <div class="sidebar-footer">

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button
                type="submit"
                class="logout-btn"
            >
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">Logout</span>
            </button>

        </form>

    </div>

</nav>
