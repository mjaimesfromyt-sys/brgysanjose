@extends('layouts.admin')
@section('title', 'Equipment Rentals')

@section('content')
<div class="mb-4">
    <h1 class="page-title">Equipment Rentals</h1>
    <p class="page-subtitle">Review rental requests and track release and return of items.</p>
</div>

@include('partials.tabs', [
    'routeName' => 'admin.rentals.index',
    'current'   => $status,
    'counts'    => $counts,
    'tabs'      => $tabs,
])

@if ($rentals->isEmpty())
    <div class="card-soft">
        <div class="empty">
            <div class="empty__title">No {{ strtolower($tabs[$status] ?? $status) }} rentals</div>
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
                        <th>Items</th>
                        <th>Dates</th>
                        <th>Purpose</th>
                        <th>Payment</th>
                        @if (in_array($status, ['approved', 'released', 'returned']))
                            <th>Claim code</th>
                        @endif
                        @if (in_array($status, ['pending', 'approved', 'released']))
                            <th class="text-end">Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rentals as $rental)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $rental->user->name }}</div>
                                <div class="text-muted small">
                                    {{ $rental->user->contact_no ?? $rental->user->email }}
                                </div>
                            </td>
                            <td>
                                <ul class="list-unstyled mb-0 small">
                                    @foreach ($rental->items as $line)
                                        <li>{{ $line->quantity }}× {{ $line->equipment->name }}</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td>
                                @if ($rental->start_date->eq($rental->end_date))
                                    {{ $rental->start_date->format('M d, Y') }}
                                @else
                                    {{ $rental->start_date->format('M d') }} –
                                    {{ $rental->end_date->format('M d, Y') }}
                                @endif
                            </td>
                            <td>{{ $rental->purpose }}</td>
                            <td>
                                @include('partials.payment-pill', ['model' => $rental, 'markPaidRoute' => route('admin.rentals.markPaid', $rental)])
                            </td>
                            @if (in_array($status, ['approved', 'released', 'returned']))
                                <td>
                                    <span class="fw-bold small" style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace">
                                        {{ $rental->claim_code ?? '—' }}
                                    </span>
                                </td>
                            @endif

                            @if ($status === 'pending')
                                <td class="text-end" style="min-width:240px">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <form method="POST" action="{{ route('admin.rentals.approve', $rental) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-success">Approve</button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="collapse" data-bs-target="#reject-{{ $rental->id }}">
                                            Reject
                                        </button>
                                    </div>

                                    <div class="collapse mt-2 text-start" id="reject-{{ $rental->id }}">
                                        <form method="POST" action="{{ route('admin.rentals.reject', $rental) }}">
                                            @csrf
                                            <textarea name="admin_remarks" rows="2" class="form-control form-control-sm mb-2"
                                                      placeholder="Reason (optional)"></textarea>
                                            <button class="btn btn-sm btn-danger w-100">Confirm rejection</button>
                                        </form>
                                    </div>
                                </td>
                            @elseif ($status === 'approved')
                                <td class="text-end">
                                    <form method="POST" action="{{ route('admin.rentals.release', $rental) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-primary">Mark released</button>
                                    </form>
                                </td>
                            @elseif ($status === 'released')
                                <td class="text-end">
                                    <form method="POST" action="{{ route('admin.rentals.return', $rental) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-success">Mark returned</button>
                                    </form>
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
