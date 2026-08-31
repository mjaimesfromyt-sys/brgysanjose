<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0b2818">
    <title>@yield('title', 'Admin') &middot; Barangay San Jose</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
@php($isAdmin = auth()->user()?->isAdmin())

<div class="admin-layout">

    <aside class="sidebar" id="adminSidebar">
        <div class="sidebar__head">
            @include('partials.seal', [
                'seal' => 'barangay', 'class' => 'seal--brand', 'fallbackClass' => 'sidebar__mark',
            ])
            <span>
                <span class="sidebar__title d-block">San Jose</span>
                <span class="sidebar__subtitle">Barangay Hall</span>
            </span>
        </div>

        <nav class="sidebar__nav" aria-label="Admin navigation">
            <div class="sidebar__label">Overview</div>
            <a class="sidebar__link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}"
               href="{{ route('dashboard') }}">
                @include('partials.icon', ['name' => 'dashboard'])
                <span>Dashboard</span>
            </a>

            @if ($isAdmin)
                {{-- Items in each queue waiting on an admin action — shown as a red count. --}}
                @php($pendingResidents = \App\Models\User::where('role', 'resident')->where('status', 'pending')->count())
                @php($pendingRequests = \App\Models\DocumentRequest::where('status', 'pending')->count())
                @php($pendingBookings = \App\Models\Booking::where('status', 'pending')->count())
                @php($pendingRentals = \App\Models\EquipmentRental::where('status', 'pending')->count())
                @php($pendingRefunds = \App\Models\RefundRequest::where('status', 'requested')->count())

                <div class="sidebar__label mt-3">Manage</div>

                <a class="sidebar__link {{ request()->routeIs('admin.residents.*') ? 'is-active' : '' }}"
                   href="{{ route('admin.residents.index') }}">
                    @include('partials.icon', ['name' => 'users'])
                    <span>Residents</span>
                    @if ($pendingResidents > 0)
                        <span class="badge rounded-pill text-bg-danger ms-auto">{{ $pendingResidents }}</span>
                    @endif
                </a>

                <a class="sidebar__link {{ request()->routeIs('admin.requests.*') ? 'is-active' : '' }}"
                   href="{{ route('admin.requests.index') }}">
                    @include('partials.icon', ['name' => 'file-text'])
                    <span>Document Requests</span>
                    @if ($pendingRequests > 0)
                        <span class="badge rounded-pill text-bg-danger ms-auto">{{ $pendingRequests }}</span>
                    @endif
                </a>

                <a class="sidebar__link {{ request()->routeIs('admin.bookings.*') ? 'is-active' : '' }}"
                   href="{{ route('admin.bookings.index') }}">
                    @include('partials.icon', ['name' => 'calendar-check'])
                    <span>Facility Bookings</span>
                    @if ($pendingBookings > 0)
                        <span class="badge rounded-pill text-bg-danger ms-auto">{{ $pendingBookings }}</span>
                    @endif
                </a>

                <a class="sidebar__link {{ request()->routeIs('admin.rentals.*') ? 'is-active' : '' }}"
                   href="{{ route('admin.rentals.index') }}">
                    @include('partials.icon', ['name' => 'package'])
                    <span>Equipment Rentals</span>
                    @if ($pendingRentals > 0)
                        <span class="badge rounded-pill text-bg-danger ms-auto">{{ $pendingRentals }}</span>
                    @endif
                </a>

                <a class="sidebar__link {{ request()->routeIs('admin.refunds.*') ? 'is-active' : '' }}"
                   href="{{ route('admin.refunds.index') }}">
                    @include('partials.icon', ['name' => 'refund'])
                    <span>Rental Refunds</span>
                    @if ($pendingRefunds > 0)
                        <span class="badge rounded-pill text-bg-danger ms-auto">{{ $pendingRefunds }}</span>
                    @endif
                </a>

                <a class="sidebar__link {{ request()->routeIs('admin.equipment.*') ? 'is-active' : '' }}"
                   href="{{ route('admin.equipment.index') }}">
                    @include('partials.icon', ['name' => 'clipboard'])
                    <span>Equipment</span>
                </a>

                <a class="sidebar__link {{ request()->routeIs('admin.facilities.*') ? 'is-active' : '' }}"
                   href="{{ route('admin.facilities.index') }}">
                    @include('partials.icon', ['name' => 'building'])
                    <span>Facilities</span>
                </a>

                <a class="sidebar__link {{ request()->routeIs('admin.transactions.*') ? 'is-active' : '' }}"
                   href="{{ route('admin.transactions.index') }}">
                    @include('partials.icon', ['name' => 'clipboard'])
                    <span>Transaction Types</span>
                </a>

                <a class="sidebar__link {{ request()->routeIs('admin.transaction-history.*') ? 'is-active' : '' }}"
                   href="{{ route('admin.transaction-history.index') }}">
                    @include('partials.icon', ['name' => 'history'])
                    <span>Transaction History</span>
                </a>

                <a class="sidebar__link {{ request()->routeIs('admin.events.*') ? 'is-active' : '' }}"
                   href="{{ route('admin.events.index') }}">
                    @include('partials.icon', ['name' => 'calendar'])
                    <span>Events</span>
                </a>

                <a class="sidebar__link {{ request()->routeIs('admin.announcements.*') ? 'is-active' : '' }}"
                   href="{{ route('admin.announcements.index') }}">
                    @include('partials.icon', ['name' => 'megaphone'])
                    <span>Announcements</span>
                </a>
            @endif

            <div class="sidebar__label mt-3">Insights</div>
            <a class="sidebar__link {{ request()->routeIs('reports.*') ? 'is-active' : '' }}"
               href="{{ route('reports.requests') }}">
                @include('partials.icon', ['name' => 'chart'])
                <span>Reports</span>
            </a>
        </nav>

        <div class="sidebar__foot">
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="sidebar__link w-100 border-0 bg-transparent text-start">
                    @include('partials.icon', ['name' => 'logout'])
                    <span>Log out</span>
                </button>
            </form>
        </div>
    </aside>

    <div class="sidebar-backdrop d-none" id="sidebarBackdrop" hidden></div>

    <div class="admin-content">
        <header class="admin-topbar d-print-none">
            <div class="container-fluid px-3 px-lg-4 d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary d-lg-none" type="button"
                        id="sidebarToggle" aria-controls="adminSidebar" aria-expanded="false"
                        aria-label="Open navigation">
                    @include('partials.icon', ['name' => 'menu', 'size' => 22])
                </button>

                <div class="ms-auto d-flex align-items-center gap-2 text-muted">
                    @include('partials.icon', ['name' => 'user', 'size' => 18])
                    <span class="fw-semibold text-dark">{{ auth()->user()->name }}</span>
                    <span class="pill pill--neutral ms-1">{{ ucfirst(auth()->user()->role) }}</span>
                </div>
            </div>
        </header>

        <main class="container-fluid px-3 px-lg-4 py-4">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>

</div>

<script>
    (function () {
        var sidebar  = document.getElementById('adminSidebar');
        var toggle   = document.getElementById('sidebarToggle');
        var backdrop = document.getElementById('sidebarBackdrop');
        if (!sidebar || !toggle || !backdrop) return;

        function setOpen(open) {
            sidebar.classList.toggle('is-open', open);
            backdrop.classList.toggle('d-none', !open);
            backdrop.hidden = !open;
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        toggle.addEventListener('click', function () {
            setOpen(!sidebar.classList.contains('is-open'));
        });
        backdrop.addEventListener('click', function () { setOpen(false); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') setOpen(false);
        });
    })();
</script>
@stack('scripts')
</body>
</html>
