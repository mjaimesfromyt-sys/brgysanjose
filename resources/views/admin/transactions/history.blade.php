@extends('layouts.admin')
@section('title', 'Transaction History')

@section('content')
<div class="mb-4">
    <h1 class="page-title">Transaction History</h1>
    <p class="page-subtitle">Search and review all payment transactions across all services.</p>
</div>

<div class="card-soft mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.transaction-history.index') }}" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Search Resident</label>
                <div class="input-group">
                    <span class="input-group-text">@include('partials.icon', ['name' => 'search', 'size' => 18])</span>
                    <input type="text" id="search" name="search" class="form-control" placeholder="Search by name, email, or contact..." value="{{ $search }}">
                </div>
            </div>

            <div class="col-md-3">
                <label for="service" class="form-label">Service</label>
                <select id="service" name="service" class="form-select">
                    <option value="">All Services</option>
                    @foreach ($services as $key => $label)
                        <option value="{{ $key }}" {{ $service === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label for="payment_status" class="form-label">Payment Status</label>
                <select id="payment_status" name="payment_status" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach ($paymentStatuses as $key => $label)
                        <option value="{{ $key }}" {{ $paymentStatus === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label for="date_from" class="form-label">Date From</label>
                <input type="date" id="date_from" name="date_from" class="form-control" value="{{ $dateFrom }}">
            </div>

            <div class="col-md-1">
                <label for="date_to" class="form-label">Date To</label>
                <input type="date" id="date_to" name="date_to" class="form-control" value="{{ $dateTo }}">
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Search</button>
                @if ($search || $service || $paymentStatus || $dateFrom || $dateTo)
                    <a href="{{ route('admin.transaction-history.index') }}" class="btn btn-outline-secondary">Clear</a>
                @endif
            </div>
        </form>
    </div>
</div>

@if ($transactions->isEmpty())
    <div class="card-soft">
        <div class="empty">
            <div class="empty__title">No transactions found</div>
            <p class="mb-0">Try adjusting your search criteria.</p>
        </div>
    </div>
@else
    <div class="table-wrap">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Resident</th>
                        <th>Service</th>
                        <th>Service Details</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Payment Status</th>
                        <th>Reference</th>
                        <th>Claim Code</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $txn)
                        <tr>
                            <td class="text-nowrap">{{ $txn['created_at']->format('M d, Y H:i') }}</td>
                            <td>
                                <div class="fw-semibold">{{ $txn['resident_name'] }}</div>
                                <div class="text-muted small">
                                    @if ($txn['resident_email'])
                                        {{ $txn['resident_email'] }}<br>
                                    @endif
                                    @if ($txn['resident_contact'])
                                        {{ $txn['resident_contact'] }}
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="pill pill--info">{{ $txn['service'] }}</span>
                            </td>
                            <td>{{ $txn['service_name'] }}</td>
                            <td class="fw-semibold">₱{{ number_format($txn['amount'], 2) }}</td>
                            <td>
                                @php
                                    $methodLabels = ['cash' => 'Cash', 'gcash' => 'GCash', 'paymaya' => 'PayMaya', 'bank_transfer' => 'Bank Transfer'];
                                @endphp
                                @if ($txn['payment_method'])
                                    <span class="pill pill--neutral">{{ $methodLabels[$txn['payment_method']] ?? ucfirst($txn['payment_method']) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusClass = $txn['payment_status'] === 'paid' ? 'pill--approved' : ($txn['payment_status'] === 'unpaid' ? 'pill--warning' : 'pill--neutral');
                                @endphp
                                <span class="pill {{ $statusClass }}">{{ ucfirst($txn['payment_status']) }}</span>
                            </td>
                            <td>
                                @if ($txn['payment_reference'])
                                    <span class="text-muted small" style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace">{{ $txn['payment_reference'] }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($txn['claim_code'])
                                    <span class="fw-bold small" style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace">{{ $txn['claim_code'] }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusLabels = [
                                        'pending' => ['label' => 'Pending', 'class' => 'pill--warning'],
                                        'approved' => ['label' => 'Approved', 'class' => 'pill--approved'],
                                        'validated' => ['label' => 'Validated', 'class' => 'pill--approved'],
                                        'released' => ['label' => 'Released', 'class' => 'pill--approved'],
                                        'returned' => ['label' => 'Returned', 'class' => 'pill--info'],
                                        'rejected' => ['label' => 'Rejected', 'class' => 'pill--danger'],
                                        'claimed' => ['label' => 'Claimed', 'class' => 'pill--approved'],
                                    ];
                                    $s = $statusLabels[$txn['status']] ?? ['label' => ucfirst($txn['status']), 'class' => 'pill--neutral'];
                                @endphp
                                <span class="pill {{ $s['class'] }}">{{ $s['label'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 text-muted small">
        Showing {{ $transactions->count() }} transaction{{ $transactions->count() !== 1 ? 's' : '' }}.
    </div>
@endif
@endsection