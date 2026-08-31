@extends('layouts.admin')
@section('title', 'Refund Requests')

@section('content')
<div class="mb-4">
    <h1 class="page-title">Rental Refund Requests</h1>
    <p class="page-subtitle">Cancellations and early returns of paid equipment rentals. Approve, set the amount, then pay it out.</p>
</div>

@include('partials.tabs', [
    'routeName' => 'admin.refunds.index',
    'current'   => $status,
    'counts'    => $counts,
    'tabs'      => $tabs,
])

@if ($refunds->isEmpty())
    <div class="card-soft">
        <div class="empty">
            <div class="empty__title">Nothing here</div>
            <p class="mb-0">Refund requests with this status will appear here.</p>
        </div>
    </div>
@else
    <div class="d-flex flex-column gap-3">
        @foreach ($refunds as $refund)
            @php($rental = $refund->rental)
            @php($estimate = \App\Support\RentalRefund::estimate($rental))
            <div class="card-soft p-3">
                <div class="d-flex flex-wrap justify-content-between gap-2">
                    <div>
                        <div class="fw-semibold">{{ $refund->user->name }}</div>
                        <div class="text-muted small">{{ $refund->user->contact_no ?? $refund->user->email }}</div>
                    </div>
                    <div class="text-end small">
                        <span class="pill {{ [
                            'requested' => 'pill--pending',
                            'approved'  => 'pill--info',
                            'refunded'  => 'pill--approved',
                            'rejected'  => 'pill--rejected',
                        ][$refund->status] ?? 'pill--neutral' }}">{{ ucfirst($refund->status) }}</span>
                        <div class="text-muted mt-1">
                            {{ $refund->type === 'early_return' ? 'Early return' : 'Cancellation before release' }}
                        </div>
                        <div class="text-muted">Requested {{ $refund->created_at->diffForHumans() }}</div>
                    </div>
                </div>

                <hr class="my-2">

                <div class="row g-3 small">
                    <div class="col-md-5">
                        <div class="text-muted">Rental #{{ $rental->id }}</div>
                        <ul class="list-unstyled mb-1">
                            @foreach ($rental->items as $line)
                                <li>{{ $line->quantity }}× {{ $line->equipment->name }}</li>
                            @endforeach
                        </ul>
                        <div class="text-muted">
                            {{ $rental->start_date->format('M d') }} – {{ $rental->end_date->format('M d, Y') }}
                            ({{ $estimate['total_days'] }} day{{ $estimate['total_days'] === 1 ? '' : 's' }})
                        </div>
                        <div class="text-muted">
                            Paid {{ ucfirst(str_replace('_', ' ', $rental->payment_method)) }} ·
                            ₱{{ number_format($rental->amount_due, 2) }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted">Resident's reason</div>
                        <div>{{ $refund->reason }}</div>
                    </div>

                    <div class="col-md-3">
                        <div class="text-muted">Refundable rental fee</div>
                        <div class="fw-bold">₱{{ number_format($estimate['refundable'], 2) }}</div>
                        @if ($refund->type === 'early_return')
                            <div class="text-muted">
                                {{ $estimate['unused_days'] }} of {{ $estimate['total_days'] }} days unused
                            </div>
                        @endif
                        <div class="text-muted">Transaction fee not refundable</div>
                        @if ($refund->amount !== null)
                            <div class="mt-1">Approved: <span class="fw-bold">₱{{ number_format($refund->amount, 2) }}</span></div>
                        @endif
                        @if ($refund->status === 'refunded')
                            <div class="text-muted">
                                via {{ $refund->refund_method }} · ref {{ $refund->refund_reference }}
                            </div>
                        @endif
                        @if ($refund->admin_remarks)
                            <div class="text-muted mt-1">Note: {{ $refund->admin_remarks }}</div>
                        @endif
                    </div>
                </div>

                @if ($refund->status === 'requested')
                    <hr class="my-2">
                    <div class="row g-2">
                        <div class="col-md-7">
                            <form method="POST" action="{{ route('admin.refunds.approve', $refund) }}"
                                  class="d-flex flex-wrap align-items-end gap-2">
                                @csrf
                                <div>
                                    <label class="form-label small mb-1" for="amount-{{ $refund->id }}">Refund amount (₱)</label>
                                    <input type="number" step="0.01" min="0" max="{{ $estimate['refundable'] }}"
                                           class="form-control form-control-sm" style="width:8rem"
                                           id="amount-{{ $refund->id }}" name="amount"
                                           value="{{ old('amount', number_format($estimate['refundable'], 2, '.', '')) }}" required>
                                </div>
                                <input type="text" name="admin_remarks" class="form-control form-control-sm" style="max-width:16rem"
                                       placeholder="Note to resident (optional)">
                                <button class="btn btn-sm btn-success">Approve</button>
                            </form>
                        </div>
                        <div class="col-md-5">
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="collapse" data-bs-target="#reject-{{ $refund->id }}">Reject</button>
                            <div class="collapse mt-2" id="reject-{{ $refund->id }}">
                                <form method="POST" action="{{ route('admin.refunds.reject', $refund) }}">
                                    @csrf
                                    <textarea name="admin_remarks" rows="2" class="form-control form-control-sm mb-2" required
                                              placeholder="Reason for rejection (shown to the resident)"></textarea>
                                    <button class="btn btn-sm btn-danger w-100">Confirm rejection</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @elseif ($refund->status === 'approved')
                    <hr class="my-2">
                    <form method="POST" action="{{ route('admin.refunds.process', $refund) }}"
                          class="d-flex flex-wrap align-items-end gap-2">
                        @csrf
                        @if ($rental->payment_method === 'cash')
                            <div>
                                <label class="form-label small mb-1" for="ref-{{ $refund->id }}">Cash refund reference</label>
                                <input type="text" class="form-control form-control-sm" style="min-width:16rem"
                                       id="ref-{{ $refund->id }}" name="manual_reference" required
                                       placeholder="e.g. OR-1234 / handed to resident">
                            </div>
                            <button class="btn btn-sm btn-primary">Mark refunded</button>
                        @else
                            <button class="btn btn-sm btn-primary">
                                Refund ₱{{ number_format($refund->amount, 2) }} via PayMongo
                            </button>
                            <div>
                                <label class="form-label small mb-1" for="ref-{{ $refund->id }}">…or record a manual reference</label>
                                <input type="text" class="form-control form-control-sm" style="min-width:14rem"
                                       id="ref-{{ $refund->id }}" name="manual_reference"
                                       placeholder="GCash / PayMongo ref (if refunded outside the app)">
                            </div>
                        @endif
                    </form>
                    <p class="text-muted small mt-2 mb-0">
                        Approving already restocked the equipment and closed the rental
                        ({{ $rental->status }}). This step only moves the money.
                    </p>
                @endif
            </div>
        @endforeach
    </div>
@endif
@endsection
