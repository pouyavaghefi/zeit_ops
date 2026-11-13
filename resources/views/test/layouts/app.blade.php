<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Ezzeit — Server Manager')</title>

    <!-- Bootstrap 5 CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* small custom touches */
        body { padding-top: 70px; }
        .card { box-shadow: 0 6px 18px rgba(0,0,0,0.06); }
        pre.command { background:#0d1117; color:#c9d1d9; padding:1rem; border-radius:6px; overflow:auto; }
        .footer { padding:1.25rem 0; margin-top:3rem; border-top:1px solid #e9ecef; }
    </style>

    @stack('styles')
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">ZeitOps</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="{{ env('APP_URL') }}">Get Back</a></li>
            </ul>
        </div>
    </div>
</nav>

<main class="py-4">
    <div class="container">
        @yield('content')
    </div>
</main>

<footer class="footer bg-light">
    <div class="container text-muted d-flex justify-content-between">
        <small>&copy; {{ date('Y') }} Ezzeit</small>
        <small>Made with ❤️ by Hack Alone Team.</small>
    </div>
</footer>

<!-- Bootstrap 5 JS bundle (Popper incl.) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
