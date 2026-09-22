{{--
    Request status timeline — visual tracker shown to residents.

    Usage:
        @include('partials.status-timeline', [
            'status'        => $req->status,              // pending|validated|claimed|rejected
            'paymentStatus' => $req->payment_status,      // paid|unpaid
            'createdAt'     => $req->created_at,
            'validatedAt'   => $req->validated_at,
            'claimedAt'     => $req->claimed_at,
            'remarks'       => $req->admin_remarks,
        ])

    Steps mirror the counter workflow: Submitted -> Paid -> Ready to claim -> Claimed.
    Rejected requests show the reason inline instead of the remaining steps.
--}}
@php
    $paid      = ($paymentStatus ?? 'unpaid') === 'paid';
    $validated = in_array($status, ['validated', 'claimed'], true);
    $claimed   = $status === 'claimed';
    $rejected  = $status === 'rejected';

    $steps = [
        ['label' => 'Submitted', 'done' => true, 'time' => $createdAt],
        ['label' => 'Paid',      'done' => $paid, 'time' => null],
        ['label' => 'Ready to claim', 'done' => $validated, 'time' => $validatedAt],
        ['label' => 'Claimed',   'done' => $claimed, 'time' => $claimedAt],
    ];
@endphp

<div class="req-timeline" aria-label="Request progress">
    @if ($rejected)
        <div class="req-timeline__rejected">
            <span class="pill pill--rejected">Rejected</span>
            @if (!empty($remarks))
                <span class="text-muted small ms-2">{{ $remarks }}</span>
            @endif
        </div>
    @else
        <div class="req-timeline__track">
            @foreach ($steps as $i => $step)
                <div class="req-step {{ $step['done'] ? 'is-done' : '' }} {{ (!$step['done'] && $i === ($paid ? ($validated ? ($claimed ? 3 : 2) : 1) : 1)) ? 'is-current' : '' }}">
                    <span class="req-step__dot">
                        @if ($step['done'])
                            <svg width="10" height="10" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0"/></svg>
                        @endif
                    </span>
                    <span class="req-step__label">{{ $step['label'] }}</span>
                </div>
                @if (!$loop->last)
                    <span class="req-step__bar {{ $steps[$i + 1]['done'] ? 'is-done' : '' }}"></span>
                @endif
            @endforeach
        </div>
    @endif
</div>

<style>
.req-timeline { min-width: 260px; }
.req-timeline__track { display: flex; align-items: center; gap: 4px; }
.req-step { display: flex; flex-direction: column; align-items: center; gap: 4px; min-width: 58px; }
.req-step__dot {
    width: 20px; height: 20px; border-radius: 50%;
    border: 2px solid #cbd5e1; background: #fff; color: #fff;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 10px; flex-shrink: 0;
}
.req-step.is-done .req-step__dot { background: #16a34a; border-color: #16a34a; }
.req-step.is-current .req-step__dot { border-color: #f59e0b; box-shadow: 0 0 0 3px rgba(245,158,11,.2); }
.req-step__label { font-size: 10.5px; color: #64748b; white-space: nowrap; }
.req-step.is-done .req-step__label { color: #166534; font-weight: 600; }
.req-step.is-current .req-step__label { color: #b45309; font-weight: 600; }
.req-step__bar { height: 2px; flex: 1 1 auto; background: #e2e8f0; margin-bottom: 16px; min-width: 10px; }
.req-step__bar.is-done { background: #16a34a; }
.req-timeline__rejected { display: flex; align-items: center; }
</style>
