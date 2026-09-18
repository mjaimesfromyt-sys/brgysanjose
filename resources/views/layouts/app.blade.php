<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#166534">
    <title>@yield('title', 'Barangay Information & Booking System')</title>
    @include('partials.favicon')
    @include('partials.google-analytics')
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])

    <style>
        body {
            background-color: #f8fafc !important;
            overflow-x: hidden;
        }
        .app-shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        /* 👉 DAKONG BARANGAY SAN JOSE SEAL SA BACKGROUND SA TANANG RESIDENT PAGES */
        .resident-global-watermark {
            position: fixed;
            top: 55%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 640px;
            height: 640px;
            background-image: url('{{ asset('images/barangay-seal.png') }}');
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
            opacity: 0.055; /* Subtle & elegant watermark */
            pointer-events: none;
            z-index: 0;
        }

        .app-main {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body>
<!-- Global Background Seal -->
<div class="resident-global-watermark"></div>

<div class="app-shell">

    <header class="topbar">
        <div class="container">
            <nav class="navbar navbar-expand-xl p-0" aria-label="Main navigation">
                <!-- Brand / Logo -->
                <a class="topbar__brand py-3" href="{{ route('home') }}">
                    @include('partials.seal', ['seal' => 'barangay', 'class' => 'seal--brand'])
                    <span>
                        Barangay San Jose
                        <span class="d-block fw-normal text-muted" style="font-size:.8rem">Talibon, Bohol</span>
                    </span>
                </a>

                {{-- MOBILE-BELL: notification stays visible beside the menu button on small screens --}}
                <div class="d-flex align-items-center gap-2 d-xl-none ms-auto">
                    @auth
                        @include('partials.notification-menu')
                    @endauth

                    <button class="navbar-toggler border-0 p-1" type="button"
                            data-bs-toggle="collapse" data-bs-target="#mainNav"
                            aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                        @include('partials.icon', ['name' => 'menu', 'size' => 26])
                    </button>
                </div>

                <div class="collapse navbar-collapse" id="mainNav">
                    <ul class="navbar-nav ms-xl-4 mb-2 mb-xl-0 gap-xl-2">
                        <li class="nav-item">
                            <a class="topbar__link {{ request()->routeIs('home') ? 'is-active' : '' }}"
                               href="{{ route('home') }}">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="topbar__link {{ request()->routeIs('announcements.*') ? 'is-active' : '' }}"
                               href="{{ route('announcements.index') }}">News</a>
                        </li>
                        <li class="nav-item">
                            <a class="topbar__link {{ request()->routeIs('map.*') ? 'is-active' : '' }}"
                               href="{{ route('map.index') }}">Barangay Map</a>
                        </li>

                        @auth
                            <li class="nav-item">
                                <a class="topbar__link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}"
                                   href="{{ route('dashboard') }}">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="topbar__link {{ request()->routeIs('events.*') ? 'is-active' : '' }}"
                                   href="{{ route('events.calendar') }}">Events</a>
                            </li>
                        @endauth
                    </ul>

                    @auth
                        <!-- Resident Profile Link & Logout -->
                        <div class="ms-xl-auto d-flex align-items-center gap-3 py-2 py-xl-0">
                            <a href="{{ route('profile.edit') }}" class="d-flex align-items-center gap-2 text-decoration-none text-dark mw-100" title="My Profile">
                                @if (auth()->user()->avatar_url)
                                    <img src="{{ auth()->user()->avatar_url }}" 
                                         alt="{{ auth()->user()->first_name }}" 
                                         class="rounded-circle shadow-sm flex-shrink-0" 
                                         style="width: 32px; height: 32px; object-fit: cover; border: 1.5px solid #1b5e20;">
                                @else
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white shadow-sm flex-shrink-0" 
                                         style="width: 32px; height: 32px; background-color: #1b5e20; font-size: 11px;">
                                        {{ auth()->user()->initials }}
                                    </div>
                                @endif
                                <span class="fw-semibold text-dark text-truncate topbar__user-name">{{ auth()->user()->name }}</span>
                            </a>

                            <div class="d-none d-xl-block">
                                @include('partials.notification-menu')
                            </div>

                            <form method="POST" action="{{ route('logout') }}" class="m-0 flex-shrink-0">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary text-nowrap">Log out</button>
                            </form>
                        </div>
                    @else
                        <div class="ms-xl-auto d-flex align-items-center gap-2 py-2 py-xl-0">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('login') }}">Log in</a>
                            <a class="btn btn-sm btn-primary" href="{{ route('register') }}">Register</a>
                        </div>
                    @endauth
                </div>
            </nav>
        </div>
    </header>

    <main class="app-main flex-grow-1">
        @hasSection('full-width')
            @if (session('success') || session('error') || $errors->any())
                <div class="container pt-4">@include('partials.flash')</div>
            @endif
            @yield('content')
        @else
            <div class="container py-4 py-lg-5">
                @include('partials.flash')
                @yield('content')
            </div>
        @endif
    </main>

    <footer class="border-top py-4 mt-auto" style="background-color: #ffffff; z-index: 1;">
        <div class="container text-center text-muted">
            <small>&copy; {{ date('Y') }} Barangay San Jose, Talibon, Bohol &middot; Information &amp; Booking System</small>
        </div>
    </footer>

</div>
@stack('scripts')

@auth
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    if (typeof Pusher === "undefined") return;
    var pusher = new Pusher("ea31042174742d209e6a", { cluster: "ap1", forceTLS: true });
    var residentId = {{ Auth::id() }};
    var channel = pusher.subscribe("resident-channel-" + residentId);

    channel.bind("status-updated", function (data) {
        try {
            var ctx = new (window.AudioContext || window.webkitAudioContext)();
            var o = ctx.createOscillator();
            var g = ctx.createGain();
            o.connect(g); g.connect(ctx.destination);
            o.frequency.setValueAtTime(783.99, ctx.currentTime);
            g.gain.setValueAtTime(0.3, ctx.currentTime);
            o.start(); o.stop(ctx.currentTime + 0.4);
        } catch (err) {}

        var toast = document.createElement("div");
        toast.style.cssText = "position:fixed;top:20px;right:20px;z-index:999999;background:#0d6efd;color:#fff;padding:16px 20px;border-radius:10px;box-shadow:0 10px 25px rgba(0,0,0,0.3);min-width:320px;font-family:sans-serif;";
        toast.innerHTML = "<div style=\"font-weight:bold;font-size:15px;\">🔔 " + (data.title || "Status Update") + "</div>" +
                          "<div style=\"margin-top:6px;font-size:13px;\">" + (data.message || "") + "</div>" +
                          "<div style=\"font-size:12px;opacity:0.85;margin-top:4px;\">Status: <strong>" + (data.status || "") + "</strong> &bull; " + (data.timestamp || "") + "</div>" +
                          "<a href=\"" + (data.url || "#") + "\" style=\"display:inline-block;margin-top:10px;padding:6px 12px;background:#fff;color:#0d6efd;border-radius:6px;text-decoration:none;font-size:12px;font-weight:bold;\">View Update &rarr;</a>";
        document.body.appendChild(toast);

        setTimeout(function () {
            toast.style.transition = "opacity 0.5s ease";
            toast.style.opacity = "0";
            setTimeout(function () { toast.remove(); }, 500);
        }, 8000);
    });
});
</script>
@endauth
</body>
</html>