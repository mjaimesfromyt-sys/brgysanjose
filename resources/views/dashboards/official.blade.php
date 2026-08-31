@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="mb-4">
    <h1 class="page-title">Barangay Official Dashboard</h1>
    <p class="page-subtitle">Monitor facility usage and service demand for community planning.</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat">
            <div class="stat__label">Active residents</div>
            <div class="stat__value">{{ number_format($residentsActive) }}</div>
            <div class="stat__meta">verified accounts</div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="stat">
            <div class="stat__label">Document requests</div>
            <div class="stat__value">{{ number_format($requestsTotal) }}</div>
            <div class="stat__meta">all time</div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="stat">
            <div class="stat__label">Awaiting action</div>
            <div class="stat__value">{{ number_format($requestsPending) }}</div>
            <div class="stat__meta">pending requests</div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="stat">
            <div class="stat__label">Upcoming bookings</div>
            <div class="stat__value">{{ number_format($bookingsUpcoming) }}</div>
            <div class="stat__meta">approved</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card-soft h-100">
            <div class="p-3 border-bottom">
                <h2 class="h6 mb-0 fw-bold">Reports</h2>
            </div>
            <div class="p-3">
                <p class="text-muted">
                    Generate and print the document requests report, filtered by date range,
                    for monitoring and planning.
                </p>
                <a href="{{ route('reports.requests') }}" class="btn btn-primary">
                    @include('partials.icon', ['name' => 'chart', 'size' => 18])
                    <span class="ms-1">Open reports</span>
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card-soft h-100">
            <div class="p-3 border-bottom">
                <h2 class="h6 mb-0 fw-bold">Upcoming events</h2>
            </div>

            @if ($upcomingEvents->isEmpty())
                <div class="empty">
                    <div class="empty__title">No upcoming events</div>
                    <p class="mb-0">Scheduled barangay activities will appear here.</p>
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
                                @if ($event->blocks_facility)
                                    <span class="pill pill--pending mt-1">Blocks a facility</span>
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
