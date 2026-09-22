<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0b2818">
    <title>@yield('title', 'Admin') &middot; Barangay San Jose</title>
    @include('partials.favicon')
    @include('partials.google-analytics')
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])

    <style>
        body {
            background-color: #f8fafc !important;
        }

        /* Watermark sa pinakaluyo (z-index: -1) aron DILI makababag sa click ug forms */
        .admin-global-watermark {
            position: fixed;
            top: 52%;
            left: calc(50% + 110px);
            transform: translate(-50%, -50%);
            width: 620px;
            height: 620px;
            background-image: url('{{ asset('images/barangay-seal.png') }}');
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
            opacity: 0.055;
            pointer-events: none;
            z-index: -1 !important;
        }

        .admin-content {
            position: relative;
            background-color: transparent !important;
        }

        /* Modal Fix: Siguroha nga ang form ma-click ug ma-edit sa ibabaw sa backdrop */
        .modal-backdrop {
            z-index: 1040 !important;
        }
        .modal {
            z-index: 1050 !important;
        }
        .modal-dialog {
            z-index: 1060 !important;
            position: relative;
        }
    </style>
</head>
<body>
@php($isAdmin = auth()->user()?->isAdmin())
@php($isSuperAdmin = auth()->user()?->isSuperAdmin())

<!-- Watermark sa pinakaluyo -->
<div class="admin-global-watermark"></div>

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

            <!-- SUPER ADMIN SECTION (UBOS SA DASHBOARD) -->
            @if ($isSuperAdmin)
                <div class="sidebar__label mt-3">Super Admin</div>

                <a class="sidebar__link {{ request()->routeIs('admin.analytics.*') ? 'is-active' : '' }}"
                   href="{{ route('admin.analytics.index') }}">
                    @include('partials.icon', ['name' => 'chart'])
                    <span>Analytics</span>
                </a>

                <a class="sidebar__link {{ request()->routeIs('admin.staff.*') ? 'is-active' : '' }}"
                   href="{{ route('admin.staff.index') }}">
                    @include('partials.icon', ['name' => 'user'])
                    <span>Staff / Sub-Admins</span>
                </a>

                <a class="sidebar__link {{ request()->routeIs('admin.settings.officials.*') ? 'is-active' : '' }}"
                   href="{{ route('admin.settings.officials.index') }}">
                    @include('partials.icon', ['name' => 'building'])
                    <span>Barangay Officials</span>
                </a>

                <a class="sidebar__link {{ request()->routeIs('admin.activity-log.*') ? 'is-active' : '' }}"
                   href="{{ route('admin.activity-log.index') }}">
                    @include('partials.icon', ['name' => 'history'])
                    <span>Activity Log</span>
                </a>
            @endif

            @if ($isAdmin)
                @php($pendingCounts = \Illuminate\Support\Facades\Cache::remember("admin.pending_counts", 30, fn () => [
                    "residents" => \App\Models\User::where("role", "resident")->where("status", "pending")->count(),
                    "requests"  => \App\Models\DocumentRequest::where("status", "pending")->count(),
                    "bookings"  => \App\Models\Booking::where("status", "pending")->count(),
                    "rentals"   => \App\Models\EquipmentRental::where("status", "pending")->count(),
                    "refunds"   => \App\Models\RefundRequest::where("status", "requested")->count(),
                ]))
                @php($pendingResidents = $pendingCounts["residents"])
                @php($pendingRequests = $pendingCounts["requests"])
                @php($pendingBookings = $pendingCounts["bookings"])
                @php($pendingRentals = $pendingCounts["rentals"])
                @php($pendingRefunds = $pendingCounts["refunds"])

                <div class="sidebar__label mt-3">Manage</div>

                <a class="sidebar__link {{ request()->routeIs('admin.residents.*') ? 'is-active' : '' }}"
                   href="{{ route('admin.residents.index') }}">
                    @include('partials.icon', ['name' => 'users'])
                    <span>Residents</span>
                    @if ($pendingResidents > 0)
                        <span class="badge rounded-pill text-bg-danger ms-auto" data-count="residents">{{ $pendingResidents }}</span>
                    @endif
                </a>

                <a class="sidebar__link {{ request()->routeIs('admin.requests.*') ? 'is-active' : '' }}"
                   href="{{ route('admin.requests.index') }}">
                    @include('partials.icon', ['name' => 'file-text'])
                    <span>Document Requests</span>
                    @if ($pendingRequests > 0)
                        <span class="badge rounded-pill text-bg-danger ms-auto" data-count="requests">{{ $pendingRequests }}</span>
                    @endif
                </a>

                <a class="sidebar__link {{ request()->routeIs('admin.bookings.*') ? 'is-active' : '' }}"
                   href="{{ route('admin.bookings.index') }}">
                    @include('partials.icon', ['name' => 'calendar-check'])
                    <span>Facility Bookings</span>
                    @if ($pendingBookings > 0)
                        <span class="badge rounded-pill text-bg-danger ms-auto" data-count="bookings">{{ $pendingBookings }}</span>
                    @endif
                </a>

                <a class="sidebar__link {{ request()->routeIs('admin.rentals.*') ? 'is-active' : '' }}"
                   href="{{ route('admin.rentals.index') }}">
                    @include('partials.icon', ['name' => 'package'])
                    <span>Equipment Rentals</span>
                    @if ($pendingRentals > 0)
                        <span class="badge rounded-pill text-bg-danger ms-auto" data-count="rentals">{{ $pendingRentals }}</span>
                    @endif
                </a>

                {{-- REFUND-HIDDEN: gitago kay ang barangay walay refund policy. Balik lang kung mo-usab.
                <a class="sidebar__link {{ request()->routeIs('admin.refunds.*') ? 'is-active' : '' }}"
                   href="{{ route('admin.refunds.index') }}">
                    @include('partials.icon', ['name' => 'refund'])
                    <span>Rental Refunds</span>
                    @if ($pendingRefunds > 0)
                        <span class="badge rounded-pill text-bg-danger ms-auto" data-count="refunds">{{ $pendingRefunds }}</span>
                    @endif
                </a>
                --}}

                @if (! $isSuperAdmin)
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

                    <a class="sidebar__link {{ request()->routeIs('admin.appointments.*') ? 'is-active' : '' }}"
                       href="{{ route('admin.appointments.index') }}">
                        @include('partials.icon', ['name' => 'calendar'])
                        <span>Captain's Appointments</span>
                    </a>

                    <a class="sidebar__link {{ request()->routeIs('admin.transactions.*') ? 'is-active' : '' }}"
                       href="{{ route('admin.transactions.index') }}">
                        @include('partials.icon', ['name' => 'clipboard'])
                        <span>Transaction Types</span>
                    </a>
                @endif

                <a class="sidebar__link {{ request()->routeIs('admin.transaction-history.*') ? 'is-active' : '' }}"
                   href="{{ route('admin.transaction-history.index') }}">
                    @include('partials.icon', ['name' => 'history'])
                    <span>Transaction History</span>
                </a>

                <a class="sidebar__link {{ request()->routeIs('admin.cash-summary.*') ? 'is-active' : '' }}"
                   href="{{ route('admin.cash-summary.index') }}"
                   title="Printable end-of-day cash collection report">
                    @include('partials.icon', ['name' => 'clipboard'])
                    <span>Daily Cash Summary</span>
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
                    @if(auth()->user()->isSuperAdmin())
                        <span class="pill pill--approved ms-1" style="background-color: #f3e8ff; color: #7e22ce; border-color: #d8b4fe;">Super Admin</span>
                    @else
                        <span class="pill pill--neutral ms-1">Sub-Admin</span>
                    @endif
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

{{-- 👉 AJAX POLLING: mo-update ang sidebar badges kada 20 seconds --}}
<script>
(function () {
    const POLL_INTERVAL = 20000;
    const pollUrl = "{{ route('admin.pendingCounts') }}";
    const labels = {
        residents: "resident registration",
        requests:  "document request",
        bookings:  "facility booking",
        rentals:   "equipment rental",
        refunds:   "refund request"
    };
    let last = {};

    document.querySelectorAll("[data-count]").forEach(function (el) {
        last[el.dataset.count] = parseInt(el.textContent.trim()) || 0;
    });

    function setBadge(key, value) {
        let badge = document.querySelector("[data-count='" + key + "']");
        if (badge) {
            if (value > 0) {
                badge.textContent = value;
                badge.style.display = "";
            } else {
                badge.style.display = "none";
            }
        }
    }

    function toast(message, url) {
        let wrap = document.getElementById("adminToastWrap");
        if (!wrap) {
            wrap = document.createElement("div");
            wrap.id = "adminToastWrap";
            wrap.style.cssText = "position:fixed;bottom:20px;right:20px;z-index:1080;display:flex;flex-direction:column;gap:10px;max-width:320px;";
            document.body.appendChild(wrap);
        }
        const t = document.createElement("a");
        t.href = url;
        t.style.cssText = "background:#166534;color:#fff;padding:12px 16px;border-radius:10px;box-shadow:0 6px 20px rgba(0,0,0,.2);text-decoration:none;font-size:13.5px;font-weight:600;animation:adminSlide .3s ease;";
        t.textContent = message;
        wrap.appendChild(t);
        setTimeout(function () { t.remove(); }, 9000);
    }

    async function poll() {
        try {
            const res = await fetch(pollUrl, { headers: { "X-Requested-With": "XMLHttpRequest" } });
            if (!res.ok) return;
            const data = await res.json();

            Object.keys(labels).forEach(function (key) {
                const value = data[key] || 0;
                setBadge(key, value);
                if (value > (last[key] || 0)) {
                    const diff = value - last[key];
                    const link = document.querySelector("[data-count='" + key + "']")?.closest("a")?.href || "#";
                    toast(diff + " new " + labels[key] + (diff > 1 ? "s" : "") + " waiting for review", link);
                }
                last[key] = value;
            });
        } catch (e) {}
    }

    setInterval(poll, POLL_INTERVAL);
})();
</script>
<style>
@keyframes adminSlide { from { opacity: 0; transform: translateX(30px); } to { opacity: 1; transform: none; } }
</style>

<!-- Pusher Real-time Alerts -->
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    if (typeof Pusher === "undefined") return;
    var pusher = new Pusher("ea31042174742d209e6a", { cluster: "ap1", forceTLS: true });
    var channel = pusher.subscribe("barangay-admin-channel");

    channel.bind("new-transaction", function (data) {
        try {
            var ctx = new (window.AudioContext || window.webkitAudioContext)();
            var osc = ctx.createOscillator();
            var gain = ctx.createGain();
            osc.connect(gain); gain.connect(ctx.destination);
            osc.frequency.setValueAtTime(587.33, ctx.currentTime);
            gain.gain.setValueAtTime(0.3, ctx.currentTime);
            osc.start();
            osc.stop(ctx.currentTime + 0.4);
        } catch (err) {}

        var toast = document.createElement("div");
        toast.style.cssText = "position:fixed;top:20px;right:20px;z-index:999999;background:#198754;color:#fff;padding:16px 20px;border-radius:10px;box-shadow:0 10px 25px rgba(0,0,0,0.3);min-width:320px;font-family:sans-serif;";
        toast.innerHTML = "<div style=\"font-weight:bold;font-size:15px;\">☎ New Transaction!</div>" +
                          "<div style=\"margin-top:6px;font-size:13px;\">" + (data.resident_name || "Resident") + " submitted a <strong>" + (data.type || "Transaction") + "</strong></div>" +
                          "<div style=\"font-size:12px;opacity:0.85;margin-top:2px;\">Time: " + (data.timestamp || "") + "</div>" +
                          "<a href=\"" + (data.url || "#") + "\" style=\"display:inline-block;margin-top:10px;padding:6px 12px;background:#fff;color:#198754;border-radius:6px;text-decoration:none;font-size:12px;font-weight:bold;\">View Now &rarr;</a>";
        document.body.appendChild(toast);

        setTimeout(function () {
            toast.style.transition = "opacity 0.5s ease";
            toast.style.opacity = "0";
            setTimeout(function () { toast.remove(); }, 500);
        }, 8000);
    });
});
</script>
</body>
</html>