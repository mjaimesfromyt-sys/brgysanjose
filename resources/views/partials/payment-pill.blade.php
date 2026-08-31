{{--
    Payment status pill + "Mark paid" action, shared by the admin rentals,
    bookings and document-requests tables.

    Expects: $model (has payment_method, payment_status, payment_reference)
    Optional: $markPaidRoute — POST route shown as a "Mark paid" button
              when the payment is unpaid cash.
--}}
@php
    $methodLabels = ['cash' => 'Cash', 'gcash' => 'GCash', 'paymaya' => 'PayMaya', 'bank_transfer' => 'Bank Transfer'];
@endphp
@if ($model->payment_method)
    <span class="pill {{ $model->payment_status === 'paid' ? 'pill--approved' : 'pill--neutral' }}">
        {{ $methodLabels[$model->payment_method] ?? ucfirst($model->payment_method) }} &middot; {{ ucfirst($model->payment_status) }}
    </span>
    @if ($model->payment_method !== 'cash' && $model->payment_reference)
        <div class="text-muted small mt-1">Ref: {{ $model->payment_reference }}</div>
    @endif
    @if ($model->payment_method === 'cash' && $model->payment_status === 'unpaid' && isset($markPaidRoute))
        <form method="POST" action="{{ $markPaidRoute }}" class="mt-1">
            @csrf
            <button class="btn btn-sm btn-outline-success">Mark paid</button>
        </form>
    @endif
@else
    <span class="text-muted">—</span>
@endif
