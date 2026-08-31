<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#166534">
    <title>@yield('title', 'Barangay Information & Booking System')</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
<div class="app-shell">

    <header class="topbar">
        <div class="container">
            {{-- expand-xl: the resident menu carries 8 destinations plus the
                 signed-in name and log-out control. It stays inline from xl up;
                 link spacing tightens in the xl band (see .topbar in app.scss)
                 so the full row fits without wrapping. --}}
            <nav class="navbar navbar-expand-xl p-0" aria-label="Main navigation">
                <a class="topbar__brand py-3" href="{{ route('home') }}">
                    @include('partials.seal', ['seal' => 'barangay', 'class' => 'seal--brand'])
                    <span>
                        Barangay San Jose
                        <span class="d-block fw-normal text-muted" style="font-size:.8rem">Talibon, Bohol</span>
                    </span>
                </a>

                <button class="navbar-toggler border-0" type="button"
                        data-bs-toggle="collapse" data-bs-target="#mainNav"
                        aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                    @include('partials.icon', ['name' => 'menu', 'size' => 26])
                </button>

                <div class="collapse navbar-collapse" id="mainNav">
                    <ul class="navbar-nav ms-xl-4 mb-2 mb-xl-0 gap-xl-1">
                        <li class="nav-item">
                            <a class="topbar__link {{ request()->routeIs('home') ? 'is-active' : '' }}"
                               href="{{ route('home') }}">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="topbar__link {{ request()->routeIs('announcements.*') ? 'is-active' : '' }}"
                               href="{{ route('announcements.index') }}">News</a>
                        </li>

                        @auth
                            <li class="nav-item">
                                <a class="topbar__link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}"
                                   href="{{ route('dashboard') }}">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="topbar__link {{ request()->routeIs('bookings.*') ? 'is-active' : '' }}"
                                   href="{{ route('bookings.index') }}">Bookings</a>
                            </li>
                            <li class="nav-item">
                                <a class="topbar__link {{ request()->routeIs('rentals.*') ? 'is-active' : '' }}"
                                   href="{{ route('rentals.index') }}">Rentals</a>
                            </li>
                            <li class="nav-item">
                                <a class="topbar__link {{ request()->routeIs('requests.*') ? 'is-active' : '' }}"
                                   href="{{ route('requests.index') }}">My Requests</a>
                            </li>
                            <li class="nav-item">
                                <a class="topbar__link {{ request()->routeIs('info.*') ? 'is-active' : '' }}"
                                   href="{{ route('info.index') }}">Requirements</a>
                            </li>
                            <li class="nav-item">
                                <a class="topbar__link {{ request()->routeIs('events.*') ? 'is-active' : '' }}"
                                   href="{{ route('events.calendar') }}">Events</a>
                            </li>
                        @endauth
                    </ul>

                    @auth
                        <div class="ms-xl-auto d-flex align-items-center gap-3 py-2 py-xl-0">
                            <span class="d-flex align-items-center gap-2 text-muted mw-100">
                                @include('partials.icon', ['name' => 'user', 'size' => 18])
                                <span class="fw-semibold text-dark text-truncate topbar__user-name">{{ auth()->user()->name }}</span>
                            </span>
                            @include('partials.notification-menu')
                            <form method="POST" action="{{ route('logout') }}" class="m-0 flex-shrink-0">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary">Log out</button>
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

    <main class="app-main">
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

    <footer class="border-top py-4 mt-auto">
        <div class="container text-center text-muted">
            <small>&copy; {{ date('Y') }} Barangay San Jose, Talibon, Bohol &middot; Information &amp; Booking System</small>
        </div>
    </footer>

</div>
@stack('scripts')
</body>
</html>
