@extends('layouts.app')
@section('title', $receipt['title'])

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 d-print-none">
            <a href="{{ $receipt['backRoute'] }}" class="text-decoration-none small fw-semibold">&larr; Back</a>
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
                @include('partials.icon', ['name' => 'print', 'size' => 18])
                <span class="ms-1">Print / Save as PDF</span>
            </button>
        </div>

        <div class="card-soft p-4">
            {{-- Letterhead — same style as the printable admin reports. --}}
            <div class="text-center mb-4">
                @include('partials.seal', ['seal' => 'barangay', 'class' => 'seal--brand'])
                <h2 class="h5 fw-bold mb-0 mt-2">Barangay San Jose</h2>
                <div class="text-muted">Talibon, Bohol</div>
                <div class="fw-semibold mt-2">{{ $receipt['title'] }}</div>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <div class="text-muted small">Resident</div>
                    <div class="fw-semibold">{{ $receipt['residentName'] }}</div>
                </div>
                <div class="col-6 text-end">
                    <div class="text-muted small">Date</div>
                    <div class="fw-semibold">{{ $receipt['date']->format('M d, Y g:i A') }}</div>
                </div>
            </div>

            <div class="table-responsive mb-3">
                <table class="table mb-0">
                    <tbody>
                        @foreach ($receipt['lines'] as $line)
                            <tr>
                                <td>{{ $line['label'] }}</td>
                                <td class="text-end">{{ $line['value'] }}</td>
                            </tr>
                        @endforeach
                        <tr class="fw-bold border-top">
                            <td>Total paid</td>
                            <td class="text-end">₱{{ number_format($receipt['amount'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @php
                $methodLabels = ['cash' => 'Cash', 'gcash' => 'GCash', 'paymaya' => 'PayMaya', 'bank_transfer' => 'Bank Transfer'];
                $channelLabels = ['gcash' => 'GCash', 'paymaya' => 'Maya', 'dob' => 'Online Banking', 'brankas' => 'Online Banking', 'card' => 'Card'];
            @endphp
            <div class="row g-2 mb-4">
                <div class="col-6">
                    <div class="text-muted small">Payment method</div>
                    <div class="fw-semibold">
                        {{ $methodLabels[$receipt['paymentMethod']] ?? ucfirst($receipt['paymentMethod']) }}
                        @if (($receipt['paymentChannel'] ?? null) && ($channelLabels[$receipt['paymentChannel']] ?? null) !== ($methodLabels[$receipt['paymentMethod']] ?? null))
                            <span class="text-muted fw-normal">({{ $channelLabels[$receipt['paymentChannel']] ?? $receipt['paymentChannel'] }})</span>
                        @endif
                    </div>
                </div>
                @if ($receipt['paymentReference'])
                    <div class="col-6 text-end">
                        <div class="text-muted small">Payment ID</div>
                        <div class="fw-semibold" style="font-size:.8rem">{{ $receipt['paymentReference'] }}</div>
                    </div>
                @endif
            </div>

            <div class="text-center p-3" style="background:#f5f7f5;border-radius:.5rem">
                <div class="text-muted small text-uppercase" style="letter-spacing:.07em">Claim code</div>
                <div class="fw-bold" style="font-size:1.75rem;font-family:ui-monospace,SFMono-Regular,Menlo,monospace">
                    {{ $receipt['claimCode'] }}
                </div>
            </div>

            <p class="text-muted small text-center mt-3 mb-0">{{ $receipt['note'] }}</p>
        </div>

    </div>
</div>
@endsection
