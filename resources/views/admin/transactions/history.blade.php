@extends('layouts.admin')
@section('title', 'Transaction History')

@section('content')
<!-- Header nga makita sa Screen -->
<div class="mb-4 d-print-none">
    <h1 class="page-title">Transaction History</h1>
    <p class="page-subtitle">Search and review all payment transactions across all services.</p>
</div>

<!-- ================= OFFICIAL PRINT HEADER (Compact & Single-Page Fit) ================= -->
<div class="d-none d-print-block mb-2 print-official-header">
    <div class="d-flex align-items-center justify-content-center pb-1 border-bottom border-success border-2 mx-auto" style="gap: 20px;">
        <!-- Left Logo: Talibon Seal -->
        <div style="flex-shrink: 0; text-align: center;">
            <img src="{{ asset('images/talibon-seal.png') }}" 
                 alt="Talibon Seal" 
                 style="width: 58px; height: 58px; object-fit: contain;">
        </div>

        <!-- Center: Official Republic Details -->
        <div class="text-center px-2">
            <div class="text-uppercase fw-semibold" style="font-size: 9.5px; letter-spacing: 0.5px; color: #444;">Republic of the Philippines</div>
            <div class="fw-semibold" style="font-size: 10px; color: #333;">Province of Bohol &bull; Municipality of Talibon</div>
            <div class="fw-bold text-uppercase" style="font-size: 15px; letter-spacing: 0.8px; color: #1b5e20;">BARANGAY SAN JOSE</div>
            <div class="text-muted" style="font-size: 9px;">Barangay Information &amp; Booking System</div>
            
            <div class="py-0.5 px-3 d-inline-block rounded mt-0.5" style="background-color: #e8f5e9; border: 1px solid #a5d6a7;">
                <span class="fw-bold" style="font-size: 10px; color: #1b5e20;">OFFICIAL TRANSACTION HISTORY REPORT</span>
            </div>
        </div>

        <!-- Right Logo: Barangay San Jose Seal -->
        <div style="flex-shrink: 0; text-align: center;">
            <img src="{{ asset('images/barangay-seal.png') }}" 
                 alt="Barangay San Jose Seal" 
                 style="width: 58px; height: 58px; object-fit: contain;">
        </div>
    </div>

    <!-- Sub-info: Date, Control Number, & Total Records -->
    <div class="d-flex justify-content-between align-items-center mt-1 text-muted px-1" style="font-size: 8.5px;">
        <span><strong>Date Generated:</strong> {{ now()->format('F d, Y h:i A') }}</span>
        <span>
            <strong>Control No.:</strong> 
            <span id="printControlNumberDisplay" class="fw-bold px-2 py-0.5 rounded" style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace; color: #1b5e20; background-color: #e8f5e9; border: 1px solid #c8e6c9;">
                Tran_{{ now()->format('m_d_y') }}-0001
            </span>
        </span>
        <span><strong>Total Records:</strong> {{ $transactions->count() }} transaction(s)</span>
    </div>
</div>

<!-- ================= FILTER CARD ================= -->
<div class="card-soft mb-4 d-print-none">
    <div class="card-body">
        <form id="transactionFilterForm" method="GET" action="{{ route('admin.transaction-history.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label for="search" class="form-label fw-semibold small text-muted">Search Resident</label>
                <div class="input-group">
                    <span class="input-group-text">@include('partials.icon', ['name' => 'search', 'size' => 18])</span>
                    <input type="text" id="search" name="search" class="form-control" placeholder="Search by name, email, or contact..." value="{{ $search }}">
                </div>
            </div>

            <div class="col-6 col-md-2">
                <label for="service" class="form-label fw-semibold small text-muted">Service</label>
                <select id="service" name="service" class="form-select">
                    <option value="">All Services</option>
                    @foreach($services as $key => $label)
                        <option value="{{ $key }}" {{ $service === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label for="payment_status" class="form-label fw-semibold small text-muted">Payment Status</label>
                <select id="payment_status" name="payment_status" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach($paymentStatuses as $key => $label)
                        <option value="{{ $key }}" {{ $paymentStatus === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label for="date_from" class="form-label fw-semibold small text-muted">Date From</label>
                <input type="date" id="date_from" name="date_from" class="form-control" value="{{ $dateFrom }}">
            </div>

            <div class="col-6 col-md-2">
                <label for="date_to" class="form-label fw-semibold small text-muted">Date To</label>
                <input type="date" id="date_to" name="date_to" class="form-control" value="{{ $dateTo }}">
            </div>

            <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2 border-top">
                <div class="d-flex gap-2">
                    @if ($search || $service || $paymentStatus || $dateFrom || $dateTo)
                        <a href="{{ route('admin.transaction-history.index') }}" class="btn btn-outline-secondary">Clear</a>
                    @endif
                </div>

                <button type="button" id="btnPrintReport" onclick="printAndLogReport()" class="btn btn-outline-dark d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
                        <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1"/>
                    </svg>
                    <span id="printBtnText">Print / Export PDF</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= TRANSACTIONS TABLE ================= -->
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
            <table class="table table-striped align-middle">
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
                    @foreach($transactions as $txn)
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
                                @if($txn['payment_status'])<span class="pill {{ $statusClass }}">{{ ucfirst($txn['payment_status']) }}</span>@else<span class="text-muted">—</span>@endif
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

    <div class="mt-2 text-muted small d-print-none">
        Showing {{ $transactions->count() }} transaction{{ $transactions->count() !== 1 ? 's' : '' }}.
    </div>
@endif

<!-- ================= STRICT 1-PAGE PRINT STYLING ================= -->
<style>
@media print {
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
        box-sizing: border-box !important;
    }

    @page {
        size: landscape;
        margin: 5mm 6mm !important;
    }

    html, body, .app-shell, .admin-layout, .wrapper, main, .content, .main-content {
        height: auto !important;
        min-height: 0 !important;
        max-height: none !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
        display: block !important;
        position: static !important;
        overflow: visible !important;
    }

    nav, aside, .sidebar, header, footer, .d-print-none, .card-soft, form, .btn, .pagination {
        display: none !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .print-official-header {
        display: block !important;
        margin-bottom: 4px !important;
        padding-bottom: 2px !important;
    }

    .table-wrap, .table-responsive {
        display: block !important;
        position: static !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: visible !important;
        border: none !important;
        box-shadow: none !important;
        break-inside: auto !important;
        page-break-inside: auto !important;
    }

    table {
        width: 100% !important;
        border-collapse: collapse !important;
        font-size: 8.5px !important;
        margin-top: 2px !important;
        break-inside: auto !important;
        page-break-inside: auto !important;
    }

    thead th {
        background-color: #e8f5e9 !important;
        color: #1b5e20 !important;
        border: 1px solid #a5d6a7 !important;
        font-weight: bold !important;
        font-size: 9px !important;
        padding: 3px 5px !important;
        white-space: nowrap !important;
    }

    th, td {
        border: 1px solid #c8e6c9 !important;
        padding: 2.5px 4px !important;
        line-height: 1.15 !important;
    }

    tbody tr {
        break-inside: avoid !important;
        page-break-inside: avoid !important;
    }

    tbody tr:nth-child(even) {
        background-color: #f9fdf9 !important;
    }

    .pill {
        border: 1px solid #888 !important;
        padding: 1px 4px !important;
        font-size: 7.5px !important;
        border-radius: 3px !important;
        display: inline-block !important;
    }
    .pill--approved { background-color: #e8f5e9 !important; color: #1b5e20 !important; border-color: #81c784 !important; }
    .pill--warning  { background-color: #fff8e1 !important; color: #b78103 !important; border-color: #ffe082 !important; }
    .pill--danger   { background-color: #ffebee !important; color: #c62828 !important; border-color: #ef9a9a !important; }
    .pill--info     { background-color: #e3f2fd !important; color: #1565c0 !important; border-color: #90caf9 !important; }
    .pill--neutral  { background-color: #f5f5f5 !important; color: #424242 !important; border-color: #e0e0e0 !important; }
}
</style>

<!-- ================= JAVASCRIPT ACTIVITY LOG TRIGGER ================= -->
<script>
function printAndLogReport() {
    const btn = document.getElementById('btnPrintReport');
    const btnText = document.getElementById('printBtnText');
    const originalText = btnText ? btnText.innerText : 'Print / Export PDF';

    if (btn) btn.disabled = true;
    if (btnText) btnText.innerText = 'Logging...';

    const searchVal = document.getElementById('search')?.value || '';
    
    const serviceSelect = document.getElementById('service');
    const serviceVal = serviceSelect ? (serviceSelect.options[serviceSelect.selectedIndex]?.text || 'All Services') : 'All Services';

    const statusSelect = document.getElementById('payment_status');
    const statusVal = statusSelect ? (statusSelect.options[statusSelect.selectedIndex]?.text || 'All Statuses') : 'All Statuses';

    const dateFrom = document.getElementById('date_from')?.value;
    const dateTo = document.getElementById('date_to')?.value;

    let dateRangeText = 'All Dates';
    if (dateFrom && dateTo) {
        dateRangeText = `${dateFrom} to ${dateTo}`;
    } else if (dateFrom) {
        dateRangeText = `From ${dateFrom}`;
    } else if (dateTo) {
        dateRangeText = `Until ${dateTo}`;
    }

    fetch("{{ route('activity_logs.log_print') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            action: 'Printed / Exported Transaction History Report',
            date_range: dateRangeText,
            service: serviceVal,
            payment_status: statusVal,
            search: searchVal
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success' && data.control_number) {
            const controlElem = document.getElementById('printControlNumberDisplay');
            if (controlElem) {
                controlElem.innerText = data.control_number;
            }
        }
    })
    .catch(error => {
        console.error('Logging error:', error);
    })
    .finally(() => {
        if (btn) btn.disabled = false;
        if (btnText) btnText.innerText = originalText;
        window.print();
    });
}
</script>
@endsection
<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("transactionFilterForm");
    if (!form) return;

    const searchInput = document.getElementById("search");
    const serviceSelect = document.getElementById("service");
    const paymentStatusSelect = document.getElementById("payment_status");
    const dateFromInput = document.getElementById("date_from");
    const dateToInput = document.getElementById("date_to");

    // Automatic submit para sa selects ug date pickers
    [serviceSelect, paymentStatusSelect, dateFromInput, dateToInput].forEach(function (elem) {
        if (elem) {
            elem.addEventListener("change", function () {
                form.submit();
            });
        }
    });

    // Debounced search para sa resident text input
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener("input", function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                form.submit();
            }, 500);
        });

        // Ibutang ang cursor focus sa tumoy sa text
        if (searchInput.value) {
            searchInput.focus();
            const val = searchInput.value;
            searchInput.value = "";
            searchInput.value = val;
        }
    }
});
</script>