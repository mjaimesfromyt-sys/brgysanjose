{{--
    Consistent status pill across bookings and document requests.
    Usage: @include('partials.status', ['status' => $booking->status])
--}}
@php
    $statusStyles = [
        'pending'   => 'pill--pending',
        'approved'  => 'pill--approved',
        'validated' => 'pill--info',
        'claimed'   => 'pill--approved',
        'rejected'  => 'pill--rejected',
        'released'  => 'pill--info',
        'returned'  => 'pill--approved',
        'active'    => 'pill--approved',
        'cancelled' => 'pill--rejected',
        'refunded'  => 'pill--approved',
        'requested' => 'pill--pending',
    ];
    $statusLabels = [
        'validated' => 'Ready to claim',
    ];
@endphp
<span class="pill {{ $statusStyles[$status] ?? 'pill--neutral' }}">
    {{ $statusLabels[$status] ?? ucfirst($status) }}
</span>
