<!DOCTYPE html>
<html lang="en">
<head>
    @include('panel.layouts.head')
</head>
<body>
<!-- Sidebar -->
<nav id="sidebar">
    @include('panel.layouts.logo')

    @include('panel.layouts.sidebar')
</nav>

<!-- Main Content -->
<div id="content">
    <!-- Topbar -->
    <div class="topbar">
        <div class="d-flex align-items-center">
            <button class="toggle-btn" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        @include('panel.layouts.actions')
    </div>

    <!-- Dashboard Content -->
    @yield('wrapper')
</div>

@include('panel.layouts.scripts')
</body>
</html>
