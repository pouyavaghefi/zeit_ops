<!DOCTYPE html>
<html lang="en">
<head>
    @include('panel.layouts.head')
</head>
<body>

{{-- Sidebar --}}
@include('panel.layouts.sidebar')

{{-- Mobile overlay --}}
<div
    id="sidebar-overlay"
    class="sidebar-overlay"
    aria-hidden="true"
></div>

{{-- Main content --}}
<div id="content">

    @include('panel.layouts.topbar')

    @yield('wrapper')

</div>

@include('panel.layouts.scripts')

</body>
</html>
