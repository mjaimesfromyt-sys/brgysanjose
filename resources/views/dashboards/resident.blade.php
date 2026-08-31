@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@php($me = auth()->user())

<div class="mb-4">
    <h1 class="page-title">Kumusta, {{ $me->first_name }}</h1>
    <p class="page-subtitle">What would you like to do today?</p>
</div>

@if ($me->status === 'rejected')
    <div class="alert alert-danger" role="alert">
        <strong>Your registration was not approved.</strong>
        @if ($me->rejection_reason)
            <br>Reason: {{ $me->rejection_reason }}
        @endif
        <br>Please visit the barangay hall or contact the office if you believe this is a mistake.
    </div>
@elseif (! $me->isActive())
    <div class="alert alert-warning" role="status">
        Your account is <strong>pending verification</strong> by the barangay.
        You can browse requirements and events, but booking, equipment rental, and document requests
        stay disabled until a staff member verifies you.
    </div>
@endif

@if ($readyToClaim->isNotEmpty())
    <div class="alert alert-success" role="status">
        <strong>Ready to claim at the barangay hall:</strong>
        <ul class="mb-0 mt-1 ps-3">
            @foreach ($readyToClaim as $doc)
                <li>
                    {{ $doc->transactionType?->name ?? 'Document' }}
                    @if ($doc->claim_code)
                        — claim code <strong>{{ $doc->claim_code }}</strong>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <a class="action-tile" href="{{ route('bookings.create') }}">
            <span class="action-tile__icon">@include('partials.icon', ['name' => 'calendar-check', 'size' => 24])</span>
            <h2 class="action-tile__title">Book a Facility</h2>
            <p class="action-tile__text">Reserve the barangay hall, covered court or conference room.</p>
            <span class="action-tile__cta">
                Book now @include('partials.icon', ['name' => 'arrow-right', 'size' => 16])
            </span>
        </a>
    </div>

    <div class="col-sm-6 col-lg-3">
        <a class="action-tile" href="{{ route('requests.create') }}">
            <span class="action-tile__icon">@include('partials.icon', ['name' => 'file-text', 'size' => 24])</span>
            <h2 class="action-tile__title">Request a Document</h2>
            <p class="action-tile__text">Apply for a clearance, certificate or permit online.</p>
            <span class="action-tile__cta">
                Start request @include('partials.icon', ['name' => 'arrow-right', 'size' => 16])
            </span>
        </a>
    </div>

    <div class="col-sm-6 col-lg-3">
        <a class="action-tile" href="{{ route('rentals.create') }}">
            <span class="action-tile__icon">@include('partials.icon', ['name' => 'package', 'size' => 24])</span>
            <h2 class="action-tile__title">Rent Equipment</h2>
            <p class="action-tile__text">Request chairs, tables, tents or other barangay items.</p>
            <span class="action-tile__cta">
                Request now @include('partials.icon', ['name' => 'arrow-right', 'size' => 16])
            </span>
        </a>
    </div>

    <div class="col-sm-6 col-lg-3">
        <a class="action-tile" href="{{ route('info.index') }}">
            <span class="action-tile__icon">@include('partials.icon', ['name' => 'clipboard', 'size' => 24])</span>
            <h2 class="action-tile__title">Requirements</h2>
            <p class="action-tile__text">See exactly what to bring before going to the hall.</p>
            <span class="action-tile__cta">
                View list @include('partials.icon', ['name' => 'arrow-right', 'size' => 16])
            </span>
        </a>
    </div>

    <div class="col-sm-6 col-lg-3">
        <a class="action-tile" href="{{ route('events.calendar') }}">
            <span class="action-tile__icon">@include('partials.icon', ['name' => 'calendar', 'size' => 24])</span>
            <h2 class="action-tile__title">Community Events</h2>
            <p class="action-tile__text">Check upcoming barangay activities and schedules.</p>
            <span class="action-tile__cta">
                Open calendar @include('partials.icon', ['name' => 'arrow-right', 'size' => 16])
            </span>
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card-soft h-100">
            <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                <h2 class="h6 mb-0 fw-bold d-flex align-items-center gap-2">
                    @include('partials.icon', ['name' => 'chart', 'size' => 18])
                    My activity
                </h2>
            </div>
            <div class="row g-0 text-center">
                <div class="col-4 p-3 border-end">
                    <div class="stat__value">{{ $myBookingsPending + $myBookingsApproved }}</div>
                    <div class="stat__meta">
                        bookings &middot; {{ $myBookingsPending }} pending
                    </div>
                    <a href="{{ route('bookings.index') }}" class="btn btn-sm btn-outline-secondary mt-2">My bookings</a>
                </div>
                <div class="col-4 p-3 border-end">
                    <div class="stat__value">{{ $myRentalsPending + $myRentalsActive }}</div>
                    <div class="stat__meta">
                        rentals &middot; {{ $myRentalsActive }} active
                    </div>
                    <a href="{{ route('rentals.index') }}" class="btn btn-sm btn-outline-secondary mt-2">My rentals</a>
                </div>
                <div class="col-4 p-3">
                    <div class="stat__value">{{ $myRequestsPending + $myRequestsReady }}</div>
                    <div class="stat__meta">
                        requests &middot; {{ $myRequestsReady }} ready
                    </div>
                    <a href="{{ route('requests.index') }}" class="btn btn-sm btn-outline-secondary mt-2">My requests</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card-soft h-100">
            <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                <h2 class="h6 mb-0 fw-bold d-flex align-items-center gap-2">
                    @include('partials.icon', ['name' => 'calendar', 'size' => 18])
                    Upcoming events
                </h2>
                <a href="{{ route('events.calendar') }}" class="small fw-semibold text-decoration-none">Calendar</a>
            </div>

            @if ($upcomingEvents->isEmpty())
                <div class="empty">
                    <div class="empty__title">No upcoming events</div>
                    <p class="mb-0">Check back soon for barangay activities.</p>
                </div>
            @else
                <ul class="list-unstyled m-0 p-2">
                    @foreach ($upcomingEvents as $event)
                        <li class="d-flex gap-3 p-2 align-items-start">
                            <div class="text-center flex-shrink-0" style="min-width:3rem">
                                <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;font-weight:700">
                                    {{ $event->start_date->format('M') }}
                                </div>
                                <div class="fw-bold" style="font-size:1.25rem;line-height:1">
                                    {{ $event->start_date->format('j') }}
                                </div>
                            </div>
                            <div class="flex-grow-1" style="min-width:0">
                                <div class="fw-semibold">{{ $event->title }}</div>
                                @if ($event->start_time)
                                    <div class="text-muted small">
                                        {{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }}
                                    </div>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection
