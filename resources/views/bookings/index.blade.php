@extends('layouts.app')
@section('title', 'My Bookings')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="page-title d-flex align-items-center gap-2">
            @include('partials.icon', ['name' => 'calendar-check', 'size' => 26])
            My Bookings
        </h1>
        <p class="page-subtitle">Facility reservations you have requested.</p>
    </div>
    <a href="{{ route('bookings.create') }}" class="btn btn-primary">
        @include('partials.icon', ['name' => 'plus', 'size' => 18])
        <span class="ms-1">New booking</span>
    </a>
</div>

@if ($bookings->isEmpty())
    <div class="card-soft">
        <div class="empty">
            @include('partials.icon', ['name' => 'calendar-check', 'size' => 32])
            <div class="empty__title mt-2">No bookings yet</div>
            <p>Reserve the barangay hall, covered court or conference room.</p>
            <a href="{{ route('bookings.create') }}" class="btn btn-primary mt-2">Book a facility</a>
        </div>
    </div>
@else
    <div class="table-wrap">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Facility</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Purpose</th>
                        <th>Claim code</th>
                        <th class="text-end">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bookings as $booking)
                        <tr>
                            <td class="fw-semibold">{{ $booking->facility->name }}</td>
                            <td>
                                @if ($booking->start_date->eq($booking->end_date))
                                    {{ $booking->start_date->format('M d, Y') }}
                                @else
                                    {{ $booking->start_date->format('M d') }} –
                                    {{ $booking->end_date->format('M d, Y') }}
                                @endif
                            </td>
                            <td class="text-muted">
                                {{ \Carbon\Carbon::parse($booking->start_time)->format('g:i A') }} –
                                {{ \Carbon\Carbon::parse($booking->end_time)->format('g:i A') }}
                            </td>
                            <td>{{ $booking->purpose }}</td>
                            <td>
                                @if ($booking->claim_code)
                                    <a href="{{ route('bookings.receipt', $booking) }}" class="small fw-semibold text-decoration-none">View receipt</a>
                                @elseif ($booking->payment_method !== 'cash' && $booking->payment_status === 'unpaid')
                                    <form method="POST" action="{{ route('bookings.pay.retry', $booking) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-primary">Pay now</button>
                                    </form>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @include('partials.status', ['status' => $booking->status])
                                @if ($booking->status === 'rejected' && $booking->admin_remarks)
                                    <div class="text-muted small mt-1">{{ $booking->admin_remarks }}</div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
