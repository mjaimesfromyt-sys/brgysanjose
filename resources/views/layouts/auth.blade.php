<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#166534">
    <title>@yield('title', 'Sign in') &middot; Barangay San Jose</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
<div class="auth-wrap">
    <div class="w-100" style="max-width:@yield('auth-width', '30rem')">

        <a href="{{ url('/') }}" class="topbar__brand justify-content-center mb-4">
            @include('partials.seal', ['seal' => 'barangay', 'class' => 'seal--brand'])
            <span>
                Barangay San Jose
                <span class="d-block fw-normal text-muted" style="font-size:.8rem">Talibon, Bohol</span>
            </span>
        </a>

        <div class="auth-card">
            @include('partials.flash')
            @yield('content')
        </div>

        <p class="text-center text-muted mt-4 mb-0">
            <small>&copy; {{ date('Y') }} Barangay Information &amp; Booking System</small>
        </p>
    </div>
</div>
@stack('scripts')
</body>
</html>
