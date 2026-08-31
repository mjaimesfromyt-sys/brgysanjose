@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="mb-4">
    <h1 class="page-title">Dashboard</h1>
    <p class="page-subtitle">{{ now()->format('l, j F Y') }}</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <a class="stat" href="{{ route('admin.residents.index') }}">
            <div class="stat__label">Active residents</div>
            <div class="stat__value">{{ number_format($residentsActive) }}</div>
            <div class="stat__meta">
                {{ $residentsPending }} awaiting verification
            </div>
        </a>
    </div>

    <div class="col-6 col-xl-3">
        <a class="stat" href="{{ route('admin.requests.index') }}">
            <div class="stat__label">Document requests</div>
            <div class="stat__value">{{ number_format($requestsPending) }}</div>
            <div class="stat__meta">pending review</div>
        </a>
    </div>

    <div class="col-6 col-xl-3">
        <a class="stat" href="{{ route('admin.bookings.index') }}">
            <div class="stat__label">Booking requests</div>
            <div class="stat__value">{{ number_format($bookingsPending) }}</div>
            <div class="stat__meta">pending approval</div>
        </a>
    </div>

    <div class="col-6 col-xl-3">
        <a class="stat" href="{{ route('admin.rentals.index') }}">
            <div class="stat__label">Equipment rentals</div>
            <div class="stat__value">{{ number_format($rentalsPending) }}</div>
            <div class="stat__meta">pending approval</div>
        </a>
    </div>

    <div class="col-6 col-xl-3">
        <a class="stat" href="{{ route('admin.bookings.index') }}">
            <div class="stat__label">In use today</div>
            <div class="stat__value">{{ number_format($bookingsToday) }}</div>
            <div class="stat__meta">approved bookings</div>
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="card-soft h-100">
            <div class="p-3 border-bottom">
                <h2 class="h6 mb-0 fw-bold d-flex align-items-center gap-2">
                    @include('partials.icon', ['name' => 'file-text', 'size' => 18])
                    Requests by status
                </h2>
            </div>
            <div class="p-3">
                @include('partials.chart.stacked', [
                    'segments' => $requestsByStatus,
                    'caption'  => 'Document requests by status',
                ])
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-soft h-100">
            <div class="p-3 border-bottom">
                <h2 class="h6 mb-0 fw-bold d-flex align-items-center gap-2">
                    @include('partials.icon', ['name' => 'building', 'size' => 18])
                    Approved bookings per facility
                </h2>
            </div>
            <div class="p-3">
                @include('partials.chart.bars', ['rows' => $bookingsByFacility])
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-soft h-100">
            <div class="p-3 border-bottom">
                <h2 class="h6 mb-0 fw-bold d-flex align-items-center gap-2">
                    @include('partials.icon', ['name' => 'chart', 'size' => 18])
                    Requests, last 6 months
                </h2>
            </div>
            <div class="p-3">
                @include('partials.chart.columns', ['points' => $requestsByMonth])
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card-soft h-100">
            <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                <h2 class="h6 mb-0 fw-bold d-flex align-items-center gap-2">
                    @include('partials.icon', ['name' => 'file-text', 'size' => 18])
                    Recent document requests
                </h2>
                <a href="{{ route('admin.requests.index') }}" class="small fw-semibold text-decoration-none">View all</a>
            </div>

            @if ($recentRequests->isEmpty())
                <div class="empty">
                    <div class="empty__title">No requests yet</div>
                    <p class="mb-0">Document requests from residents will appear here.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Resident</th>
                                <th>Document</th>
                                <th>Submitted</th>
                                <th class="text-end">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentRequests as $req)
                                <tr>
                                    <td class="fw-semibold">{{ $req->user?->name ?? '—' }}</td>
                                    <td>{{ $req->transactionType?->name ?? '—' }}</td>
                                    <td class="text-muted">{{ $req->created_at?->diffForHumans() }}</td>
                                    <td class="text-end">
                                        @include('partials.status', ['status' => $req->status])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card-soft h-100">
            <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                <h2 class="h6 mb-0 fw-bold d-flex align-items-center gap-2">
                    @include('partials.icon', ['name' => 'calendar-check', 'size' => 18])
                    Upcoming bookings
                </h2>
                <a href="{{ route('admin.bookings.index') }}" class="small fw-semibold text-decoration-none">View all</a>
            </div>

            @if ($upcomingBookings->isEmpty())
                <div class="empty">
                    <div class="empty__title">Nothing scheduled</div>
                    <p class="mb-0">Approved bookings will show up here.</p>
                </div>
            @else
                <ul class="list-unstyled m-0 p-2">
                    @foreach ($upcomingBookings as $booking)
                        <li class="d-flex gap-3 p-2 align-items-start">
                            <div class="text-center flex-shrink-0" style="min-width:3rem">
                                <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;font-weight:700">
                                    {{ $booking->start_date->format('M') }}
                                </div>
                                <div class="fw-bold" style="font-size:1.25rem;line-height:1">
                                    {{ $booking->start_date->format('j') }}
                                </div>
                            </div>
                            <div class="flex-grow-1" style="min-width:0">
                                <div class="fw-semibold">{{ $booking->facility?->name ?? 'Facility' }}</div>
                                <div class="text-muted small">
                                    {{ \Carbon\Carbon::parse($booking->start_time)->format('g:i A') }} –
                                    {{ \Carbon\Carbon::parse($booking->end_time)->format('g:i A') }}
                                </div>
                                <div class="text-muted small">{{ $booking->user?->name }}</div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection
