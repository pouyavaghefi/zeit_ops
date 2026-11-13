<!DOCTYPE html>
<html lang="en">
<head>
    @include('panel.layouts.head')
</head>
<body>
<!-- Sidebar -->
@include('panel.layouts.sidebar')

<!-- Main Content -->
<div id="content">
    <!-- Topbar -->
    @include('panel.layouts.topbar')

    <!-- Dashboard Content -->
    @yield('wrapper')
</div>

@include('panel.layouts.scripts')

</body>
</html>
