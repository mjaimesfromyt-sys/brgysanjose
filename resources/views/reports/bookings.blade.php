@extends('layouts.admin')
@section('title', 'Facility Bookings Report')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 d-print-none">
    <div>
        <h1 class="page-title">Facility Bookings Report</h1>
        <p class="page-subtitle">Filter by period, then print or save as PDF.</p>
    </div>
    <button onclick="window.print()" class="btn btn-outline-secondary">
        @include('partials.icon', ['name' => 'print', 'size' => 18])
        <span class="ms-1">Print / Save as PDF</span>
    </button>
</div>

@include('reports._tabs')

<div class="card-soft p-3 mb-4 d-print-none">
    <form method="GET" action="{{ route('reports.bookings') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label for="from" class="form-label">From</label>
            <input id="from" type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="form-control">
        </div>
        <div class="col-md-4">
            <label for="to" class="form-label">To</label>
            <input id="to" type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="form-control">
        </div>
        <div class="col-md-4">
            <button class="btn btn-primary w-100">Apply filter</button>
        </div>
    </form>
</div>

{{-- Report letterhead — carries the context onto the printed page. --}}
<div class="mb-4">
    <h2 class="h5 fw-bold mb-1">Barangay San Jose &middot; Talibon, Bohol</h2>
    <div class="fw-semibold">Facility Bookings Report</div>
    <p class="text-muted mb-0">
        Period: {{ $from->format('M d, Y') }} – {{ $to->format('M d, Y') }}
    </p>
    <p class="text-muted mb-0">Generated: {{ now()->format('M d, Y g:i A') }}</p>
</div>

<div class="row g-3 mb-4">
    @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $key => $label)
        <div class="col-6 col-md-4">
            <div class="stat text-center">
                <div class="stat__value">{{ $statusCounts[$key] }}</div>
                <div class="stat__label mt-1">{{ $label }}</div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card-soft h-100">
            <div class="p-3 border-bottom">
                <h3 class="h6 mb-0 fw-bold">Bookings by facility</h3>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <tbody>
                        @forelse ($byFacility as $row)
                            <tr>
                                <td>{{ $row['name'] }}</td>
                                <td class="text-end fw-semibold">{{ $row['count'] }}</td>
                            </tr>
                        @empty
                            <tr><td class="text-muted">No data for this period.</td></tr>
                        @endforelse
                        <tr class="fw-bold border-top">
                            <td>Total</td>
                            <td class="text-end">{{ $total }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card-soft h-100">
            <div class="p-3 border-bottom">
                <h3 class="h6 mb-0 fw-bold">Detailed list</h3>
            </div>

            @if ($bookings->isEmpty())
                <div class="empty">
                    <div class="empty__title">No bookings in this period</div>
                    <p class="mb-0">Try widening the date range.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Resident</th>
                                <th>Facility</th>
                                <th>Status</th>
                                <th>Amount</th>
                                <th>Code</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bookings as $booking)
                                <tr>
                                    <td class="text-muted">{{ $booking->created_at->format('M d') }}</td>
                                    <td>{{ $booking->user->name }}</td>
                                    <td>{{ $booking->facility->name }}</td>
                                    <td>{{ ucfirst($booking->status) }}</td>
                                    <td>₱{{ number_format($booking->amount_due, 2) }}</td>
                                    <td>{{ $booking->claim_code ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
