@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@php($me = auth()->user())

<!-- 👉 DAKO UG MAS MAKLARONG BARANGAY SEAL SA BACKGROUND -->
<div class="dashboard-watermark"></div>

<style>
    body {
        background-color: #f8fafc !important;
    }

    /* 1. Klaro ug Dako nga Background Watermark */
    .dashboard-watermark {
        position: fixed;
        top: 54%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 640px;
        height: 640px;
        background-image: url('{{ asset('images/barangay-seal.png') }}');
        background-repeat: no-repeat;
        background-position: center;
        background-size: contain;
        opacity: 0.075; /* Mas maklaro na gyud ang logo sa luyo */
        pointer-events: none;
        z-index: 0;
    }

    .dash-content-wrapper {
        position: relative;
        z-index: 1;
    }

    /* 2. Welcome Hero Card uban ang Selyo sa Ibabaw */
    .welcome-hero-card {
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(4px);
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 24px 26px;
        box-shadow: 0 4px 20px -4px rgba(0,0,0,0.05);
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }
    .welcome-hero-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #166534, #22c55e, #3b82f6);
    }

    .brgy-seal-badge {
        width: 76px;
        height: 76px;
        object-fit: contain;
        filter: drop-shadow(0 6px 12px rgba(22, 101, 52, 0.18));
        transition: transform 0.25s ease;
    }
    .brgy-seal-badge:hover {
        transform: scale(1.08) rotate(3deg);
    }

    /* 3. Service Action Tiles */
    .service-tile {
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(4px);
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 22px 20px;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        position: relative;
        overflow: hidden;
    }
    .service-tile::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3.5px;
    }
    .tile-facility::before { background: linear-gradient(90deg, #0284c7, #38bdf8); }
    .tile-doc::before      { background: linear-gradient(90deg, #16a34a, #4ade80); }
    .tile-rental::before   { background: linear-gradient(90deg, #d97706, #fbbf24); }
    .tile-req::before      { background: linear-gradient(90deg, #9333ea, #c084fc); }
    .tile-event::before    { background: linear-gradient(90deg, #e11d48, #fb7185); }

    .service-tile:hover {
        transform: translateY(-5px);
        box-shadow: 0 14px 28px -6px rgba(0,0,0,0.09);
        border-color: #cbd5e1;
        background: #ffffff;
    }

    .tile-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
        transition: transform 0.2s;
    }
    .service-tile:hover .tile-icon-box {
        transform: scale(1.1);
    }
    .box-facility { background-color: #f0f9ff; color: #0284c7; }
    .box-doc      { background-color: #f0fdf4; color: #16a34a; }
    .box-rental   { background-color: #fffbeb; color: #d97706; }
    .box-req      { background-color: #faf5ff; color: #9333ea; }
    .box-event    { background-color: #fff1f2; color: #e11d48; }

    .tile-title {
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 6px;
    }
    .tile-desc {
        font-size: 13px;
        color: #64748b;
        line-height: 1.45;
        margin-bottom: 16px;
        flex-grow: 1;
    }
    .tile-cta {
        font-size: 13px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: gap 0.2s;
    }
    .service-tile:hover .tile-cta {
        gap: 10px;
    }

    /* 4. Claim Alert Banner */
    .claim-alert-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(240, 253, 244, 0.95));
        border: 1px solid #86efac;
        border-left: 5px solid #16a34a;
        border-radius: 16px;
        padding: 18px 22px;
        box-shadow: 0 4px 15px rgba(22, 163, 74, 0.06);
    }

    /* 5. Dashboard Cards */
    .dash-card {
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(4px);
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 22px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.02);
    }

    .stat-widget {
        border-radius: 14px;
        padding: 16px 12px;
        transition: all 0.2s;
        border: 1px solid transparent;
    }
    .stat-widget:hover {
        transform: translateY(-2px);
    }
    .widget-bookings { background-color: #f0f9ff; border-color: #bae6fd; }
    .widget-rentals  { background-color: #fffbeb; border-color: #fde68a; }
    .widget-requests { background-color: #f0fdf4; border-color: #bbf7d0; }

    .stat-number {
        font-size: 32px;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 4px;
    }
</style>

<div class="dash-content-wrapper">

    <!-- ================= WELCOME HERO BANNER ================= -->
    <div class="welcome-hero-card">
        <div class="d-flex flex-nowrap justify-content-between align-items-start gap-2 gap-md-3">
            <div class="d-flex align-items-center gap-3">
                @if ($me->avatar_url)
                    <img src="{{ $me->avatar_url }}" alt="{{ $me->first_name }}" class="rounded-circle shadow-sm" style="width: 58px; height: 58px; object-fit: cover; border: 2.5px solid #16a34a;">
                @else
                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 58px; height: 58px; background-color: #e8f5e9; color: #166534; font-size: 22px;">
                        {{ $me->initials }}
                    </div>
                @endif
                <div>
                    <h1 class="h4 fw-bold text-dark mb-1">Kumusta, {{ $me->name }} 👋</h1>
                    <div class="d-flex flex-column flex-sm-row flex-sm-wrap align-items-start align-items-sm-center gap-1 gap-sm-2 text-muted small">
                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fw-bold">
                            ✓ Verified Resident
                        </span>
                        @if ($me->purok)
                            <span class="fw-semibold text-dark"><span class="d-none d-sm-inline">&bull; </span>{{ str_starts_with(strtolower($me->purok), 'purok') ? $me->purok : 'Purok ' . $me->purok }}</span>
                        @endif
                        <span><span class="d-none d-sm-inline">&bull; </span>Barangay San Jose, Talibon, Bohol</span>
                    </div>
                </div>
            </div>

            <!-- Right: Opisyal nga Selyo & Petsa -->
            <div class="d-flex align-items-center gap-3 ms-auto flex-shrink-0">
                <div class="text-end d-none d-md-block">
                    <div class="text-uppercase fw-bold text-success" style="font-size: 11px; letter-spacing: 0.8px;">Barangay San Jose</div>
                    <div class="text-dark fw-bold" style="font-size: 13.5px;">Official Resident Portal</div>
                    <div class="text-muted small" style="font-size: 12px;">{{ now()->format('l, F j, Y') }}</div>
                </div>
                <img src="{{ asset('images/barangay-seal.png') }}" 
                     alt="Barangay San Jose Seal" 
                     class="brgy-seal-badge"
                     title="Official Seal of Barangay San Jose, Talibon">
            </div>
        </div>
    </div>

    <!-- System Alerts -->
    @if ($me->status === 'rejected')
        <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4" role="alert">
            <strong>Your registration was not approved.</strong>
            @if ($me->rejection_reason)
                <br>Reason: {{ $me->rejection_reason }}
            @endif
            <br>Please visit the barangay hall or contact the office if you believe this is a mistake.
        </div>
    @elseif (! $me->isActive())
        <div class="alert alert-warning border-0 shadow-sm rounded-3 mb-4" role="status">
            Your account is <strong>pending verification</strong> by the barangay.
            You can browse requirements and events, but booking, equipment rental, and document requests
            stay disabled until a staff member verifies you.
        </div>
    @endif

    <!-- Ready to Claim Banner -->
    @if ($readyToClaim->isNotEmpty())
        <div class="claim-alert-card mb-4">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span style="font-size: 22px;">📜</span>
                <strong class="text-success" style="font-size: 15.5px;">Ready to claim at the barangay hall:</strong>
            </div>
            <div class="d-flex flex-wrap gap-2 pt-1">
                @foreach ($readyToClaim as $doc)
                    <div class="d-flex align-items-center gap-2 bg-white px-3 py-2 rounded-pill shadow-sm border" style="font-size: 13px;">
                        <span class="fw-bold text-dark">{{ $doc->transactionType?->name ?? 'Document' }}</span>
                        @if ($doc->claim_code)
                            <span class="badge bg-success font-monospace px-2.5 py-1">{{ $doc->claim_code }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- ================= ROW 1: 4 MAIN SERVICE TILES ================= -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <a class="service-tile tile-facility" href="{{ route('bookings.create') }}">
                <div>
                    <div class="tile-icon-box box-facility">
                        @include('partials.icon', ['name' => 'calendar-check', 'size' => 24])
                    </div>
                    <div class="tile-title">Book a Facility</div>
                    <div class="tile-desc">Reserve the barangay hall, covered court or conference room.</div>
                </div>
                <div class="tile-cta text-primary">
                    Book now @include('partials.icon', ['name' => 'arrow-right', 'size' => 15])
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-lg-3">
            <a class="service-tile tile-doc" href="{{ route('requests.create') }}">
                <div>
                    <div class="tile-icon-box box-doc">
                        @include('partials.icon', ['name' => 'file-text', 'size' => 24])
                    </div>
                    <div class="tile-title">Request a Document</div>
                    <div class="tile-desc">Apply for a clearance, certificate or permit online with QR code.</div>
                </div>
                <div class="tile-cta text-success">
                    Start request @include('partials.icon', ['name' => 'arrow-right', 'size' => 15])
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-lg-3">
            <a class="service-tile tile-rental" href="{{ route('rentals.create') }}">
                <div>
                    <div class="tile-icon-box box-rental">
                        @include('partials.icon', ['name' => 'package', 'size' => 24])
                    </div>
                    <div class="tile-title">Rent Equipment</div>
                    <div class="tile-desc">Request chairs, tables, tents or other barangay supplies.</div>
                </div>
                <div class="tile-cta text-warning-emphasis">
                    Request now @include('partials.icon', ['name' => 'arrow-right', 'size' => 15])
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-lg-3">
            <a class="service-tile tile-req" href="{{ route('appointments.create') }}">
                <div>
                    <div class="tile-icon-box box-req" style="background-color: rgba(147, 51, 234, 0.1); color: #9333ea;">
                        @include('partials.icon', ['name' => 'calendar', 'size' => 24])
                    </div>
                    <div class="tile-title">Captain's Appointment</div>
                    <div class="tile-desc">Book a formal consultation, mediation (husay), or hearing.</div>
                </div>
                <div class="tile-cta text-purple" style="color: #9333ea;">
                    Book appointment @include('partials.icon', ['name' => 'arrow-right', 'size' => 15])
                </div>
            </a>
        </div>
    </div>

    <!-- ================= ROW 2: COMMUNITY EVENTS & MY ACTIVITY ================= -->
    <div class="row g-3 mb-4">
        <div class="col-md-5 col-lg-4">
            <a class="service-tile tile-event" href="{{ route('events.calendar') }}">
                <div>
                    <div class="tile-icon-box box-event">
                        @include('partials.icon', ['name' => 'calendar', 'size' => 24])
                    </div>
                    <div class="tile-title">Community Events</div>
                    <div class="tile-desc">Check upcoming barangay assemblies, festivities, and official schedules.</div>
                </div>
                <div class="tile-cta text-danger">
                    Open calendar @include('partials.icon', ['name' => 'arrow-right', 'size' => 15])
                </div>
            </a>
        </div>

        <div class="col-md-7 col-lg-8">
            <div class="dash-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-3">
                    <h6 class="mb-0 fw-bold d-flex align-items-center gap-2 text-dark" style="font-size: 15.5px;">
                        <span style="font-size: 18px;">📊</span> My Activity
                    </h6>
                    <span class="badge bg-light text-muted border px-2.5 py-1 small">Personal Overview</span>
                </div>

                <div class="row g-3 text-center flex-grow-1 align-items-center">
                    <div class="col-4">
                        <div class="stat-widget widget-bookings">
                            <div class="stat-number text-primary">{{ $myBookingsPending + $myBookingsApproved }}</div>
                            <div class="small fw-bold text-dark mb-1">Bookings</div>
                            <div class="badge bg-primary text-white mb-2" style="font-size: 10px;">
                                {{ $myBookingsPending }} pending
                            </div>
                            <div>
                                <a href="{{ route('bookings.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-0.5 fw-semibold" style="font-size: 11.5px;">My bookings</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="stat-widget widget-rentals">
                            <div class="stat-number text-warning-emphasis">{{ $myRentalsPending + $myRentalsActive }}</div>
                            <div class="small fw-bold text-dark mb-1">Rentals</div>
                            <div class="badge bg-warning text-dark mb-2" style="font-size: 10px;">
                                {{ $myRentalsActive }} active
                            </div>
                            <div>
                                <a href="{{ route('rentals.index') }}" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-3 py-0.5 fw-semibold" style="font-size: 11.5px;">My rentals</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="stat-widget widget-requests">
                            <div class="stat-number text-success">{{ $myRequestsPending + $myRequestsReady }}</div>
                            <div class="small fw-bold text-dark mb-1">Requests</div>
                            <div class="badge bg-success text-white mb-2" style="font-size: 10px;">
                                {{ $myRequestsReady }} ready
                            </div>
                            @if (!empty($latestActiveRequest))
                                <div class="mb-2 text-start small text-muted" title="Latest request: {{ $latestActiveRequest->transactionType->name ?? '' }}">
                                    <span class="fw-semibold text-dark">Latest:</span>
                                    {{ $latestActiveRequest->transactionType->name ?? 'Request' }}
                                    <span class="badge bg-success-subtle text-success border border-success-subtle ms-1" style="font-size: 10px;">{{ ucfirst($latestActiveRequest->status) }}</span>
                                </div>
                            @endif
                            <div>
                                <a href="{{ route('requests.index') }}" class="btn btn-sm btn-outline-success rounded-pill px-3 py-0.5 fw-semibold" style="font-size: 11.5px;">My requests</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= ROW 3: UPCOMING EVENTS LIST ================= -->
    <div class="row g-3">
        <div class="col-12">
            <div class="dash-card">
                <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-2">
                    <h6 class="mb-0 fw-bold d-flex align-items-center gap-2 text-dark" style="font-size: 15.5px;">
                        <span style="font-size: 18px;">📅</span> Upcoming Events
                    </h6>
                    <a href="{{ route('events.calendar') }}" class="small fw-bold text-success text-decoration-none">View Full Calendar &rarr;</a>
                </div>

                @if ($upcomingEvents->isEmpty())
                    <div class="py-4 text-center text-muted">
                        <div class="small">No upcoming events scheduled at this moment.</div>
                    </div>
                @else
                    <div class="p-1">
                        <ul class="list-unstyled m-0">
                            @foreach ($upcomingEvents as $event)
                                <li class="d-flex gap-3 p-2.5 align-items-center border-bottom last-border-0">
                                    <div class="text-center flex-shrink-0 p-2 rounded-3 border shadow-sm" style="min-width: 3.5rem; background-color: #f0fdf4; border-color: #bbf7d0 !important;">
                                        <div class="text-success" style="font-size: .72rem; text-transform: uppercase; font-weight: 800;">
                                            {{ $event->start_date->format('M') }}
                                        </div>
                                        <div class="fw-bold text-dark" style="font-size: 1.3rem; line-height: 1;">
                                            {{ $event->start_date->format('j') }}
                                        </div>
                                    </div>
                                    <div class="flex-grow-1" style="min-width:0;">
                                        <div class="fw-bold text-dark" style="font-size: 14.5px;">{{ $event->title }}</div>
                                        @if ($event->start_time)
                                            <div class="text-muted small">
                                                ⏰ {{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }}
                                            </div>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection

<style>
@media (max-width: 575.98px) {
    .welcome-hero-card .brgy-seal-badge {
        width: 64px;
        height: 64px;
    }
}
</style>


<style>
/* Mobile: i-align ang seal sa Verified Resident badge, dili sa title */
@media (max-width: 575.98px) {
    .welcome-hero-card .brgy-seal-badge {
        margin-top: 62px;
    }
}
/* seal-lower-mobile */
</style>
