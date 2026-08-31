@extends('layouts.app')
@section('title', 'My Equipment Rentals')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="page-title d-flex align-items-center gap-2">
            @include('partials.icon', ['name' => 'package', 'size' => 26])
            My Equipment Rentals
        </h1>
        <p class="page-subtitle">Chairs, tables, tents and other barangay equipment you have requested.</p>
    </div>
    <a href="{{ route('rentals.create') }}" class="btn btn-primary">
        @include('partials.icon', ['name' => 'plus', 'size' => 18])
        <span class="ms-1">New rental request</span>
    </a>
</div>

@if ($rentals->isEmpty())
    <div class="card-soft">
        <div class="empty">
            @include('partials.icon', ['name' => 'package', 'size' => 32])
            <div class="empty__title mt-2">No rentals yet</div>
            <p>Request chairs, tables, tents or other equipment for your event.</p>
            <a href="{{ route('rentals.create') }}" class="btn btn-primary mt-2">Request equipment</a>
        </div>
    </div>
@else
    <div class="table-wrap">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Items</th>
                        <th>Dates</th>
                        <th>Purpose</th>
                        <th>Claim code</th>
                        <th class="text-end">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rentals as $rental)
                        @php($refund = $rental->refundRequests->sortByDesc('id')->first())
                        <tr>
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
                                @if ($rental->claim_code)
                                    <a href="{{ route('rentals.receipt', $rental) }}" class="small fw-semibold text-decoration-none">View receipt</a>
                                @elseif ($rental->payment_method !== 'cash' && $rental->payment_status === 'unpaid')
                                    <form method="POST" action="{{ route('rentals.pay.retry', $rental) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-primary">Pay now</button>
                                    </form>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @include('partials.status', ['status' => $rental->status])
                                @if ($rental->status === 'rejected' && $rental->admin_remarks)
                                    <div class="text-muted small mt-1">{{ $rental->admin_remarks }}</div>
                                @endif

                                @if ($refund)
                                    <div class="mt-2 small">
                                        <span class="pill {{ [
                                            'requested' => 'pill--pending',
                                            'approved'  => 'pill--info',
                                            'refunded'  => 'pill--approved',
                                            'rejected'  => 'pill--rejected',
                                        ][$refund->status] ?? 'pill--neutral' }}">
                                            Refund {{ $refund->status }}
                                        </span>
                                        @if ($refund->status === 'refunded')
                                            <div class="text-muted mt-1">
                                                ₱{{ number_format($refund->amount, 2) }} refunded · ref {{ $refund->refund_reference }}
                                            </div>
                                        @elseif ($refund->status === 'approved')
                                            <div class="text-muted mt-1">
                                                ₱{{ number_format($refund->amount, 2) }} approved — awaiting payout
                                            </div>
                                        @elseif ($refund->status === 'rejected' && $refund->admin_remarks)
                                            <div class="text-muted mt-1">{{ $refund->admin_remarks }}</div>
                                        @endif
                                    </div>
                                @endif

                                @if ($rental->isRefundEligible())
                                    <div class="mt-2">
                                        <button class="btn btn-sm btn-outline-secondary" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#refund-{{ $rental->id }}"
                                                aria-expanded="false" aria-controls="refund-{{ $rental->id }}">
                                            @include('partials.icon', ['name' => 'refund', 'size' => 16])
                                            <span class="ms-1">Request cancellation / refund</span>
                                        </button>

                                        <div class="collapse mt-2 text-start" id="refund-{{ $rental->id }}">
                                            <form method="POST" action="{{ route('rentals.refund.request', $rental) }}">
                                                @csrf
                                                <label class="form-label small fw-semibold mb-1" for="reason-{{ $rental->id }}">
                                                    Why are you cancelling{{ $rental->status === 'released' ? ' / returning early' : '' }}?
                                                </label>
                                                <textarea class="form-control form-control-sm" id="reason-{{ $rental->id }}"
                                                          name="reason" rows="3" required minlength="10"
                                                          placeholder="e.g. Our event was postponed and we no longer need the equipment.">{{ old('reason') }}</textarea>
                                                <p class="text-muted small mt-1 mb-2">
                                                    The rental fee{{ $rental->status === 'released' ? ' for the unused days' : '' }} is refundable;
                                                    the transaction fee is not. The barangay confirms the final amount.
                                                </p>
                                                <button class="btn btn-sm btn-primary">Submit request</button>
                                            </form>
                                        </div>
                                    </div>
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
