@extends('layouts.admin')
@section('title', 'Facility Bookings')

@section('content')
<div class="mb-4">
    <h1 class="page-title">Facility Bookings</h1>
    <p class="page-subtitle">Review and decide on resident reservation requests.</p>
</div>

@include('partials.tabs', [
    'routeName' => 'admin.bookings.index',
    'current'   => $status,
    'counts'    => $counts,
    'tabs'      => ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'],
])

@if ($bookings->isEmpty())
    <div class="card-soft">
        <div class="empty">
            <div class="empty__title">No {{ $status }} bookings</div>
            <p class="mb-0">Requests with this status will appear here.</p>
        </div>
    </div>
@else
    <div class="table-wrap">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Resident</th>
                        <th>Facility</th>
                        <th>Dates</th>
                        <th>Time</th>
                        <th>Purpose</th>
                        <th>Payment</th>
                        @if ($status === 'approved')<th>Claim code</th>@endif
                        @if ($status === 'pending')<th class="text-end">Decision</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bookings as $booking)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $booking->user->name }}</div>
                                <div class="text-muted small">
                                    {{ $booking->user->contact_no ?? $booking->user->email }}
                                </div>
                            </td>
                            <td>{{ $booking->facility->name }}</td>
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
                                @include('partials.payment-pill', ['model' => $booking, 'markPaidRoute' => route('admin.bookings.markPaid', $booking)])
                            </td>
                            @if ($status === 'approved')
                                <td>
                                    <span class="fw-bold small" style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace">
                                        {{ $booking->claim_code ?? '—' }}
                                    </span>
                                </td>
                            @endif

                            @if ($status === 'pending')
                                <td class="text-end" style="min-width:240px">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <form method="POST" action="{{ route('admin.bookings.approve', $booking) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-success">Approve</button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="collapse" data-bs-target="#reject-{{ $booking->id }}">
                                            Reject
                                        </button>
                                    </div>

                                    <div class="collapse mt-2 text-start" id="reject-{{ $booking->id }}">
                                        <form method="POST" action="{{ route('admin.bookings.reject', $booking) }}">
                                            @csrf
                                            <textarea name="admin_remarks" rows="2" class="form-control form-control-sm mb-2"
                                                      placeholder="Reason (optional)"></textarea>
                                            <button class="btn btn-sm btn-danger w-100">Confirm rejection</button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
