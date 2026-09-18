@extends('layouts.app')

@section('title', 'Rent Equipment — Barangay San Jose')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">

            {{-- Hero Header uban ang SVG Icon --}}
            <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 52px; height: 52px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="#198754" viewBox="0 0 16 16">
                            <path d="M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.305.914a1 1 0 0 0 .242 1.023l3.27 3.27a.997.997 0 0 0 1.414 0l1.586-1.586a.997.997 0 0 0 0-1.414l-3.27-3.27a1 1 0 0 0-1.023-.242L10.5 9.5l-.96-.96 2.68-2.643A3.005 3.005 0 0 0 16 3c0-.269-.035-.53-.102-.777l-2.14 2.141L12 4l-.364-1.757L13.777.102a3 3 0 0 0-3.675 3.68L7.462 6.46 4.793 3.793a1 1 0 0 1-.293-.707v-.071a1 1 0 0 0-.419-.814zm9.646 10.646a.5.5 0 0 1 .708 0l2.914 2.915a.5.5 0 0 1-.707.707l-2.915-2.914a.5.5 0 0 1 0-.708M3 11l.471.242.529.026.287.445.445.287.026.529L5 13l-.242.471-.026.529-.445.287-.287.445-.529.026L3 15l-.471-.242-.529-.026-.287-.445-.445-.287-.026-.529L1 13l.242-.471.026-.529.445-.287.287-.445.529-.026z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0 text-dark">Barangay Equipment Rental</h3>
                        <p class="text-muted small mb-0">Select dates, equipment quantity, and review your itemized checkout breakdown below.</p>
                    </div>
                </div>
                <a href="{{ route('rentals.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    &larr; My Rentals
                </a>
            </div>

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                    <strong>Notice:</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger rounded-3 shadow-sm mb-4">
                    <div class="fw-bold mb-1">Please check the form below:</div>
                    <ul class="mb-0 ps-3 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('rentals.store') }}" method="POST" id="rentalForm">
                @csrf

                {{-- Card 1: Rental Schedule --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-dark mb-3">1. Rental Schedule</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Start Date <span class="text-danger">*</span></label>
                                <!-- 👉 SAKTONG NAME: start_date -->
                                <input type="date" name="start_date" id="rental_start_date" 
                                    class="form-control rounded-3 @error('start_date') is-invalid @enderror"
                                    value="{{ old('start_date', now()->format('Y-m-d')) }}" 
                                    min="{{ now()->format('Y-m-d') }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">End Date <span class="text-danger">*</span></label>
                                <!-- 👉 SAKTONG NAME: end_date -->
                                <input type="date" name="end_date" id="rental_end_date" 
                                    class="form-control rounded-3 @error('end_date') is-invalid @enderror"
                                    value="{{ old('end_date', now()->format('Y-m-d')) }}" 
                                    min="{{ now()->format('Y-m-d') }}" required>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mt-2 text-muted small" id="durationDisplay">
                            Rental Duration: <strong class="text-dark" id="daysCount">1 day</strong>
                            <div class="small text-muted mt-1">
                                <i class="bi bi-clock me-1"></i>Pickup: <strong>8:00 AM</strong> &middot; Return: <strong>5:00 PM</strong> (katapusan nga adlaw)
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Select Items --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-dark mb-3">2. Select Equipment & Quantity</h5>

                        <div class="table-responsive">
                            <table class="table align-middle table-hover mb-0">
                                <thead class="table-light text-secondary small text-uppercase">
                                    <tr>
                                        <th style="min-width: 220px;">Equipment Item</th>
                                        <th class="text-center" style="width: 140px;">Available Stock</th>
                                        <th class="text-center" style="width: 140px;">Official Rate</th>
                                        <th class="text-end" style="width: 150px;">Quantity to Rent</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($equipment as $index => $item)
                                        @php
                                            $isMixer = str_contains(strtolower($item->name), 'mixer');
                                            $rateUnit = $isMixer ? '/ day' : 'each';
                                            $available = $item->available_stock ?? $item->total_stock;
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $item->name }}</div>
                                                @if(!empty($item->description))
                                                    <div class="text-muted small">{{ Str::limit($item->description, 50) }}</div>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($available > 0)
                                                    <span class="badge bg-success bg-opacity-10 text-success fw-semibold px-2 py-1 rounded-pill">
                                                        {{ $available }} available
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger bg-opacity-10 text-danger fw-semibold px-2 py-1 rounded-pill">
                                                        Out of stock
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center fw-bold text-success">
                                                ₱{{ number_format($item->fee, 2) }}
                                                <span class="text-muted small fw-normal">{{ $rateUnit }}</span>
                                            </td>
                                            <td class="text-end">
                                                <!-- 👉 SAKTONG NESTED INPUTS: items[index][equipment_id] ug items[index][quantity] -->
                                                <input type="hidden" name="items[{{ $index }}][equipment_id]" value="{{ $item->id }}">
                                                <input type="number" 
                                                    name="items[{{ $index }}][quantity]" 
                                                    class="form-control form-control-sm text-end rounded-3 rental-qty" 
                                                    style="max-width: 120px; margin-left: auto;"
                                                    min="0" 
                                                    max="{{ $available }}" 
                                                    value="{{ old('items.' . $index . '.quantity') }}"
                                                    placeholder="0"
                                                    data-id="{{ $item->id }}"
                                                    data-name="{{ $item->name }}"
                                                    data-fee="{{ $item->fee }}"
                                                    data-per-day="{{ $isMixer ? '1' : '0' }}"
                                                    data-available="{{ $available }}"
                                                    {{ $available <= 0 ? 'disabled' : '' }}>
                                                <div class="qty-warning text-danger small mt-1" style="display: none; max-width: 120px; margin-left: auto;"></div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                No equipment available for rental at this time.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Card 3: Purpose --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-dark mb-3">3. Purpose of Rental</h5>
                        <textarea name="purpose" class="form-control rounded-3 @error('purpose') is-invalid @enderror" 
                            rows="2" placeholder="State your purpose (e.g., House construction, Family reunion, Birthday gathering...)" required>{{ old('purpose') }}</textarea>
                        @error('purpose')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- CARD 4: LIVE CHECKOUT SUMMARY TABLE --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                    <div class="card-header bg-light bg-opacity-50 py-3 px-4 border-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-dark mb-0">4. Rental Checkout Summary</h5>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 small">
                            Itemized Order Breakdown
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <div id="checkoutEmptyNotice" class="text-center py-4 text-muted">
                            <span class="fw-semibold">No equipment selected yet.</span>
                            <div class="small">Enter the quantity of equipment you want to rent above to preview your checkout computation.</div>
                        </div>

                        <div id="checkoutTableWrap" style="display: none;">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0" style="border-color: #e2e8f0;">
                                    <thead style="background-color: #f8fafc;">
                                        <tr class="text-secondary small text-uppercase">
                                            <th class="ps-3 py-2">Item / Equipment</th>
                                            <th class="py-2 text-center" style="width: 140px;">Unit Rate</th>
                                            <th class="py-2 text-center" style="width: 160px;">Quantity & Days</th>
                                            <th class="pe-3 py-2 text-end" style="width: 150px;">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody id="checkoutTableBody"></tbody>
                                    <tfoot>
                                        <tr class="table-light">
                                            <td colspan="3" class="text-end fw-semibold text-secondary py-2">Equipment Rental Subtotal:</td>
                                            <td class="text-end fw-bold text-dark pe-3 py-2" id="checkoutSubtotal">₱0.00</td>
                                        </tr>
                                        <tr id="checkoutCashlessRow" class="table-light" style="display: none;">
                                            <td colspan="3" class="text-end text-muted small py-1">Cashless Online Processing Fee:</td>
                                            <td class="text-end text-muted small pe-3 py-1" id="checkoutCashlessFee">₱10.00</td>
                                        </tr>
                                        <tr style="background-color: #f0fdf4; border-top: 2px solid #16a34a;">
                                            <td colspan="3" class="text-end fw-bold fs-6 text-success py-3">Total Amount to Pay:</td>
                                            <td class="text-end pe-3 py-3">
                                                <span class="fw-bold fs-4 text-success" id="checkoutFinalTotal">₱0.00</span>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 5: Payment Method --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-dark mb-3">5. Preferred Payment Method</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="card h-100 border p-3 rounded-3 cursor-pointer payment-card" for="pay_cash">
                                    <div class="d-flex align-items-center gap-3">
                                        <input class="form-check-input mt-0" type="radio" name="payment_method" id="pay_cash" value="cash" checked>
                                        <div>
                                            <div class="fw-bold text-dark">Cash at Barangay Hall</div>
                                            <div class="small text-muted">Pay directly to the Barangay Treasurer upon claiming.</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="card h-100 border p-3 rounded-3 cursor-pointer payment-card" for="pay_cashless">
                                    <div class="d-flex align-items-center gap-3">
                                        <input class="form-check-input mt-0" type="radio" name="payment_method" id="pay_cashless" value="gcash">
                                        <div>
                                            <div class="fw-bold text-dark">Online Cashless (GCash / Maya)</div>
                                            <div class="small text-muted">Convenient instant digital checkout (+₱10 online fee).</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="alert alert-info d-flex align-items-start gap-2 py-2 px-3 mt-3 mb-0 small" role="note">
                            <span style="font-size: 16px; line-height: 1.2;">&#128161;</span>
                            <span>
                                <strong>Tip:</strong> Paying online saves you a trip &mdash; your payment is confirmed
                                instantly, so you only visit the barangay hall to pick up the equipment.
                                Cash payments are only confirmed once you pay at the counter.
                            </span>
                        </div>
                    </div>
                </div>

                {{-- 👉 TERMS AND CONDITIONS --}}
                <div class="card shadow-sm rounded-4 mb-4" style="border: 2px solid #f0ad4e; background-color: #fffdf5;">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark mb-2">&#9888;&#65039; TERMS AND CONDITIONS</h6>
                        <p class="text-muted small mb-3">
                            Payment for barangay services is <strong class="text-danger">non-refundable</strong>
                            once the request has been approved and paid. Please make sure all details are correct before submitting.
                        </p>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="agreeTerms" name="agree_terms" value="1" required>
                            <label class="form-check-label fw-semibold text-dark small" for="agreeTerms">
                                I have read and understood the terms and conditions.
                            </label>
                        </div>
                    </div>
                </div>
                {{-- Submit Button --}}
                <div class="d-grid gap-2 mb-5">
                    <button type="submit" class="btn btn-success btn-lg rounded-pill fw-bold py-3 shadow-sm" id="submitBtn">
                        Submit Equipment Rental Request &rarr;
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const startDateInput = document.getElementById('rental_start_date');
    const endDateInput = document.getElementById('rental_end_date');
    const daysCountEl = document.getElementById('daysCount');
    const qtyInputs = document.querySelectorAll('.rental-qty');
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');

    const emptyNotice = document.getElementById('checkoutEmptyNotice');
    const tableWrap = document.getElementById('checkoutTableWrap');
    const tbody = document.getElementById('checkoutTableBody');
    const subtotalEl = document.getElementById('checkoutSubtotal');
    const cashlessRow = document.getElementById('checkoutCashlessRow');
    const finalTotalEl = document.getElementById('checkoutFinalTotal');

    const cashlessFee = 10.00;

    function getDaysCount() {
        if (!startDateInput.value || !endDateInput.value) return 1;
        const start = new Date(startDateInput.value);
        const end = new Date(endDateInput.value);
        const diffTime = end - start;
        const days = Math.floor(diffTime / (1000 * 60 * 60 * 24)) + 1;
        return days > 0 ? days : 1;
    }

    function isCashless() {
        let cashless = false;
        paymentRadios.forEach(function (radio) {
            if (radio.checked && radio.value !== 'cash') {
                cashless = true;
            }
        });
        return cashless;
    }

    function updateCheckout() {
        const days = getDaysCount();
        daysCountEl.textContent = days + (days === 1 ? ' day' : ' days');

        let subtotal = 0;
        let selectedItems = [];

        qtyInputs.forEach(function (input) {
            const qty = parseInt(input.value) || 0;
            const fee = parseFloat(input.dataset.fee) || 0;
            const name = input.dataset.name || 'Equipment';
            const isPerDay = input.dataset.perDay === '1';

            if (qty > 0) {
                let itemTotal = 0;
                let durationText = '';

                if (isPerDay) {
                    itemTotal = qty * fee * days;
                    durationText = qty + ' unit(s) × ' + days + ' day(s)';
                } else {
                    itemTotal = qty * fee;
                    durationText = qty + ' unit(s)';
                }

                subtotal += itemTotal;
                selectedItems.push({
                    name: name,
                    rate: '₱' + fee.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + (isPerDay ? ' / day' : ' each'),
                    duration: durationText,
                    total: '₱' + itemTotal.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                });
            }
        });

        const cashlessSelected = isCashless();
        const finalTotal = subtotal + (cashlessSelected ? cashlessFee : 0);

        if (selectedItems.length > 0) {
            emptyNotice.style.display = 'none';
            tableWrap.style.display = 'block';

            let rowsHtml = '';
            selectedItems.forEach(function (item) {
                rowsHtml += '<tr>' +
                    '<td class="ps-3 py-2 fw-semibold text-dark">' + item.name + '</td>' +
                    '<td class="py-2 text-center text-muted">' + item.rate + '</td>' +
                    '<td class="py-2 text-center text-dark">' + item.duration + '</td>' +
                    '<td class="pe-3 py-2 text-end fw-bold text-success">' + item.total + '</td>' +
                '</tr>';
            });
            tbody.innerHTML = rowsHtml;

            subtotalEl.textContent = '₱' + subtotal.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            if (cashlessSelected) {
                cashlessRow.style.display = 'table-row';
            } else {
                cashlessRow.style.display = 'none';
            }

            finalTotalEl.textContent = '₱' + finalTotal.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        } else {
            emptyNotice.style.display = 'block';
            tableWrap.style.display = 'none';
            subtotalEl.textContent = '₱0.00';
            cashlessRow.style.display = 'none';
            finalTotalEl.textContent = '₱0.00';
        }
    }

    startDateInput.addEventListener('change', function () {
        if (endDateInput.value < startDateInput.value) {
            endDateInput.value = startDateInput.value;
        }
        endDateInput.min = startDateInput.value;
        updateCheckout();
    });

    endDateInput.addEventListener('change', updateCheckout);
    qtyInputs.forEach(input => input.addEventListener('input', updateCheckout));

    // 👉 STOCK WARNING: mo-notify kung lapas sa available stock
    qtyInputs.forEach(input => {
        input.addEventListener('input', function () {
            const available = parseInt(this.dataset.available || 0);
            const entered = parseInt(this.value || 0);
            const warningBox = this.parentElement.querySelector('.qty-warning');
            if (entered > available) {
                this.value = available;
                warningBox.textContent = 'Only ' + available + ' available';
                warningBox.style.display = 'block';
                this.classList.add('is-invalid');
                setTimeout(() => {
                    warningBox.style.display = 'none';
                    this.classList.remove('is-invalid');
                }, 3000);
                updateCheckout();
            } else {
                warningBox.style.display = 'none';
                this.classList.remove('is-invalid');
            }
        });
    });
    paymentRadios.forEach(radio => radio.addEventListener('change', updateCheckout));

    updateCheckout();
});
</script>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    const agree = document.getElementById('agreeTerms');
    const btn   = document.getElementById('submitBtn');
    if (!agree || !btn) return;

    function sync() {
        btn.disabled = !agree.checked;
        btn.classList.toggle('opacity-50', !agree.checked);
        btn.style.cursor = agree.checked ? '' : 'not-allowed';
        btn.title = agree.checked ? '' : 'Please accept the terms and conditions first.';
    }

    agree.addEventListener('change', sync);
    sync();
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[data-available]').forEach(function (inp) {
        inp.addEventListener('focus', function () { this.select(); });
    });
});
</script>
