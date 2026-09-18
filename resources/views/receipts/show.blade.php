@extends('layouts.app')
@section('title', $receipt['title'])

@section('content')
<style>
    @media print {
        header, footer, .d-print-none, .btn {
            display: none !important;
        }
        .receipt-card {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }
    }
    .receipt-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.05);
        padding: 36px 32px;
        position: relative;
    }
</style>

<div class="row justify-content-center py-3">
    <div class="col-lg-7">

        <!-- Action Bar (Print / Back) -->
        <div class="d-flex justify-content-between align-items-center mb-3 d-print-none">
            <a href="{{ $receipt['backRoute'] ?? url()->previous() }}" class="text-decoration-none small fw-bold text-success d-inline-flex align-items-center gap-1">
                &larr; Back
            </a>
            <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-dark d-inline-flex align-items-center gap-1.5 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1"/></svg>
                Print Receipt
            </button>
        </div>

        <!-- ================= OFFICIAL RECEIPT SLIP ================= -->
        <div class="receipt-card">
            <!-- Header with Official Seal -->
            <div class="text-center pb-3 border-bottom mb-4">
                <img src="{{ asset('images/barangay-seal.png') }}" alt="Barangay Seal" style="width: 68px; height: 68px; object-fit: contain; margin-bottom: 8px;">
                <div class="text-uppercase text-muted" style="font-size: 11px; letter-spacing: 0.8px;">Republic of the Philippines &bull; Province of Bohol</div>
                <h2 class="h5 fw-bold text-success mb-0" style="letter-spacing: 0.5px;">BARANGAY SAN JOSE</h2>
                <div class="small text-muted mb-2">Municipality of Talibon &bull; Office of the Punong Barangay</div>
                <div class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 fw-bold" style="font-size: 12px;">
                    OFFICIAL {{ strtoupper($receipt['title']) }}
                </div>
            </div>

            <!-- 👉 DASHED GREEN CLAIM CODE HIGHLIGHT (Parehas gyud sa Bookings) -->
            <div class="p-3 rounded-3 text-center mb-4" style="background-color: #f0fdf4; border: 1.5px dashed #86efac;">
                <div class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Official Claim Code</div>
                <div class="display-6 fw-bold font-monospace text-success my-1">{{ $receipt['claimCode'] }}</div>
                <div class="text-muted small" style="font-size: 11.5px;">{{ $receipt['note'] ?? 'Present this receipt and claim code to the barangay hall.' }}</div>
            </div>

            <!-- Details Table -->
            <table class="table table-borderless mb-4" style="font-size: 13.5px;">
                <tbody>
                    <tr class="border-bottom">
                        <td class="text-muted ps-0 py-2">Resident Name:</td>
                        <td class="fw-semibold text-dark text-end py-2">{{ $receipt['residentName'] }}</td>
                    </tr>
                    <tr class="border-bottom">
                        <td class="text-muted ps-0 py-2">Date &amp; Time:</td>
                        <td class="text-dark text-end py-2">{{ $receipt['date']->format('F d, Y g:i A') }}</td>
                    </tr>

                    <!-- Item Lines (e.g. 1x Chairs) -->
                    @if (!empty($receipt['lines']))
                        @foreach ($receipt['lines'] as $line)
                            <tr class="border-bottom">
                                <td class="text-dark fw-bold ps-0 py-2">{{ $line['label'] }}</td>
                                <td class="fw-bold text-dark text-end py-2">{{ $line['value'] }}</td>
                            </tr>
                        @endforeach
                    @endif

                    @php
                        $methodLabels = ['cash' => 'Cash', 'gcash' => 'GCash', 'paymaya' => 'PayMaya', 'bank_transfer' => 'Bank Transfer'];
                        $channelLabels = ['gcash' => 'GCash', 'paymaya' => 'Maya', 'dob' => 'Online Banking', 'brankas' => 'Online Banking', 'card' => 'Card'];
                    @endphp

                    <tr class="border-bottom">
                        <td class="text-muted ps-0 py-2">Payment Details:</td>
                        <td class="text-end py-2">
                            <span class="badge bg-success px-2.5 py-1">PAID</span>
                            <span class="text-dark fw-semibold ms-1">
                                {{ $methodLabels[$receipt['paymentMethod']] ?? ucfirst($receipt['paymentMethod']) }}
                                @if (($receipt['paymentChannel'] ?? null) && ($channelLabels[$receipt['paymentChannel']] ?? null) !== ($methodLabels[$receipt['paymentMethod']] ?? null))
                                    <span class="text-muted small fw-normal">({{ $channelLabels[$receipt['paymentChannel']] ?? $receipt['paymentChannel'] }})</span>
                                @endif
                            </span>
                        </td>
                    </tr>

                    @if (!empty($receipt['paymentReference']))
                        <tr class="border-bottom">
                            <td class="text-muted ps-0 py-2">Reference ID:</td>
                            <td class="text-muted small font-monospace text-end py-2">{{ $receipt['paymentReference'] }}</td>
                        </tr>
                    @endif

                    <tr>
                        <td class="text-dark fw-bold fs-6 ps-0 pt-3">Total Amount:</td>
                        <td class="fw-bold fs-5 text-success text-end pt-3">
                            ₱{{ number_format($receipt['amount'], 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Slip Footer -->
            <div class="pt-3 border-top text-center text-muted small" style="font-size: 11px;">
                <div>Barangay Hall Address: <strong>Purok 5, San Jose, Talibon, Bohol</strong></div>
                <div>Emergency / Tanod Desk: 24/7 &bull; Official Email: blgusanjosetalibon1910@gmail.com</div>
            </div>
        </div>

    </div>
</div>
@endsection
