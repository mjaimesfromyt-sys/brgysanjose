@extends('layouts.app')

@section('title', 'Book a Facility — Barangay San Jose')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">

            {{-- Hero Header uban ang SVG Icon --}}
            <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 52px; height: 52px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="#198754" viewBox="0 0 16 16">
                            <path d="M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8"/>
                            <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0 text-dark">Book a Barangay Facility</h3>
                        <p class="text-muted small mb-0">Reserve the Basketball Covered Court, Session Hall, or Gym with real-time slot availability.</p>
                    </div>
                </div>
                <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    &larr; My Bookings
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
                    <div class="fw-bold mb-1">Please fix the issue below:</div>
                    <ul class="mb-0 ps-3 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('bookings.store') }}" id="bookingForm">
                @csrf

                {{-- 👉 STEP 1: SELECT FACILITY --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-dark mb-3">1. Select Barangay Facility</h5>
                        <label for="facility_id" class="form-label small fw-semibold text-secondary">Facility <span class="text-danger">*</span></label>
                        <select id="facility_id" name="facility_id" class="form-select form-select-lg rounded-3 @error('facility_id') is-invalid @enderror" required>
                            @foreach ($facilities as $facility)
                                <option value="{{ $facility->id }}" 
                                    data-name="{{ $facility->name }}"
                                    {{ old('facility_id', $facilities->first()->id ?? '') == $facility->id ? 'selected' : '' }}>
                                    {{ $facility->name }}@if($facility->capacity) (capacity {{ $facility->capacity }})@endif
                                    — ₱50/hr Daytime &bull; ₱100/hr Nighttime &bull; ₱1,000/whole day
                                </option>
                            @endforeach
                        </select>
                        @error('facility_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- 👉 STEP 2: RESERVATION SCHEDULE & TIME (WITH LIVE TIMELINE TRACKER) --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-dark mb-3">2. Reservation Schedule & Time</h5>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="start_date" class="form-label small fw-semibold text-secondary">Start Date <span class="text-danger">*</span></label>
                                <input id="start_date" type="date" name="start_date"
                                       value="{{ old('start_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}"
                                       class="form-control rounded-3 @error('start_date') is-invalid @enderror" required>
                                @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="end_date" class="form-label small fw-semibold text-secondary">End Date <span class="text-danger">*</span></label>
                                <input id="end_date" type="date" name="end_date"
                                       value="{{ old('end_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}"
                                       class="form-control rounded-3 @error('end_date') is-invalid @enderror" required>
                                @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- 👉 REAL-TIME COURT TIMELINE / SLOT AVAILABILITY WIDGET --}}
                        <div class="p-3 bg-light rounded-3 border mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-dark small d-flex align-items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#198754" viewBox="0 0 16 16">
                                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                                    </svg>
                                    Court Schedule & Slot Availability on: <span id="timelineSelectedDate" class="text-success ms-1">Today</span>
                                </span>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary small" id="slotStatusIndicator">Checking slots...</span>
                            </div>
                            
                            <div id="timelineSlotsList" class="d-flex flex-column gap-2 small">
                                <div class="text-muted small py-1">Loading schedule...</div>
                            </div>
                        </div>

                        {{-- Conflict Warning Notice (Hidden by default) --}}
                        <div id="slotConflictAlert" class="alert alert-danger py-2 px-3 rounded-3 small mb-3 shadow-sm" style="display: none;">
                            <strong>Conflict Warning:</strong> Your selected time slot overlaps with an existing booking/event. Please adjust your time above.
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="start_time" class="form-label small fw-semibold text-secondary">Start Time <span class="text-danger">*</span></label>
                                <input id="start_time" type="time" name="start_time"
                                       value="{{ old('start_time', '08:00') }}"
                                       class="form-control rounded-3 @error('start_time') is-invalid @enderror" required>
                                @error('start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="end_time" class="form-label small fw-semibold text-secondary">End Time <span class="text-danger">*</span></label>
                                <input id="end_time" type="time" name="end_time"
                                       value="{{ old('end_time', '09:00') }}"
                                       class="form-control rounded-3 @error('end_time') is-invalid @enderror" required>
                                @error('end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mt-3 text-muted small">
                            Rate Structure: <span class="badge bg-success bg-opacity-10 text-success me-1">Daytime: ₱50/hr (6am-6pm)</span> <span class="badge bg-primary bg-opacity-10 text-primary me-1">Nighttime: ₱100/hr w/ lights (6pm-11pm)</span> <span class="badge bg-warning bg-opacity-10 text-warning-emphasis">Whole Day: ₱1,000 (8+ hrs)</span>
                        </div>
                    </div>
                </div>

                {{-- 👉 STEP 3: PURPOSE OF RESERVATION --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-dark mb-3">3. Purpose of Reservation</h5>
                        <label for="purpose" class="form-label small fw-semibold text-secondary">Purpose / Activity <span class="text-danger">*</span></label>
                        <input id="purpose" type="text" name="purpose"
                               value="{{ old('purpose') }}"
                               placeholder="e.g. Basketball Tournament, Youth Assembly, Birthday Gathering, Barangay League..."
                               class="form-control rounded-3 @error('purpose') is-invalid @enderror" required>
                        @error('purpose') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- 👉 STEP 4: BOOKING CHECKOUT SUMMARY TABLE --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                    <div class="card-header bg-light bg-opacity-50 py-3 px-4 border-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-dark mb-0">4. Booking Checkout Summary</h5>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 small" id="rateTypeBadge">
                            Daytime Hourly Rate
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0" style="border-color: #e2e8f0;">
                                <thead style="background-color: #f8fafc;">
                                    <tr class="text-secondary small text-uppercase">
                                        <th class="ps-3 py-2">Facility</th>
                                        <th class="py-2 text-center" style="width: 220px;">Rate Breakdown</th>
                                        <th class="py-2 text-center" style="width: 160px;">Duration</th>
                                        <th class="pe-3 py-2 text-end" style="width: 150px;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="ps-3 py-3 fw-bold text-dark" id="checkoutFacilityName">Covered Court</td>
                                        <td class="text-center py-3 text-muted small" id="checkoutRateApplied">₱50.00 / hr (Daytime)</td>
                                        <td class="text-center py-3 text-dark" id="checkoutDuration">1 hr &bull; 1 day</td>
                                        <td class="pe-3 py-3 text-end fw-bold text-dark" id="checkoutSubtotal">₱50.00</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="table-light">
                                        <td colspan="3" class="text-end fw-semibold text-secondary py-2">Facility Rental Subtotal:</td>
                                        <td class="text-end fw-bold text-dark pe-3 py-2" id="checkoutBaseSubtotal">₱50.00</td>
                                    </tr>
                                    <tr id="checkoutCashlessRow" class="table-light" style="display: none;">
                                        <td colspan="3" class="text-end text-muted small py-1">Cashless Online Processing Fee:</td>
                                        <td class="text-end text-muted small pe-3 py-1">₱10.00</td>
                                    </tr>
                                    <tr style="background-color: #f0fdf4; border-top: 2px solid #16a34a;">
                                        <td colspan="3" class="text-end fw-bold fs-6 text-success py-3">Total Amount to Pay:</td>
                                        <td class="text-end pe-3 py-3">
                                            <span class="fw-bold fs-4 text-success" id="checkoutTotal">₱50.00</span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- 👉 STEP 5: PREFERRED PAYMENT METHOD (DUHA RA KA BOXES) --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-dark mb-3">5. Preferred Payment Method</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="card h-100 border p-3 rounded-3 cursor-pointer payment-card" for="pay_cash">
                                    <div class="d-flex align-items-center gap-3">
                                        <input class="form-check-input mt-0 payment-radio" type="radio" name="payment_method" id="pay_cash" value="cash" checked>
                                        <div>
                                            <div class="fw-bold text-dark">Cash at Barangay Hall</div>
                                            <div class="small text-muted">Pay directly to the Barangay Treasurer upon booking.</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="card h-100 border p-3 rounded-3 cursor-pointer payment-card" for="pay_cashless">
                                    <div class="d-flex align-items-center gap-3">
                                        <input class="form-check-input mt-0 payment-radio" type="radio" name="payment_method" id="pay_cashless" value="gcash">
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
                                instantly, so your reservation moves straight to approval.
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
                        Submit Booking Request &rarr;
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const facilitySelect = document.getElementById('facility_id');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    const paymentRadios = document.querySelectorAll('.payment-radio');
    const submitBtn = document.getElementById('submitBtn');

    // Timeline elements
    const timelineDateEl = document.getElementById('timelineSelectedDate');
    const timelineListEl = document.getElementById('timelineSlotsList');
    const slotStatusIndicator = document.getElementById('slotStatusIndicator');
    const conflictAlert = document.getElementById('slotConflictAlert');

    // Summary elements
    const rateTypeBadge = document.getElementById('rateTypeBadge');
    const facilityNameEl = document.getElementById('checkoutFacilityName');
    const rateAppliedEl = document.getElementById('checkoutRateApplied');
    const durationEl = document.getElementById('checkoutDuration');
    const subtotalEl = document.getElementById('checkoutSubtotal');
    const baseSubtotalEl = document.getElementById('checkoutBaseSubtotal');
    const cashlessRow = document.getElementById('checkoutCashlessRow');
    const totalEl = document.getElementById('checkoutTotal');

    const cashlessFee = 10.00;
    let activeScheduleData = { events: [], bookings: [] };

    function format12(timeStr) {
        if (!timeStr) return '';
        const [h, m] = timeStr.split(':').map(Number);
        const ampm = h >= 12 ? 'PM' : 'AM';
        const h12 = h % 12 || 12;
        return h12 + ':' + (m < 10 ? '0' : '') + m + ' ' + ampm;
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

    // 👉 FETCH SCHEDULE PARA SA NAPILING ADLAW
    function fetchSchedule() {
        const date = startDateInput.value;
        const fid = facilitySelect.value;
        if (!date) return;

        timelineDateEl.textContent = date;
        slotStatusIndicator.textContent = 'Updating schedule...';

        fetch('/bookings/schedule?facility_id=' + fid + '&date=' + date)
            .then(res => res.json())
            .then(data => {
                activeScheduleData = data;
                renderTimeline();
                checkConflict();
            })
            .catch(() => {
                timelineListEl.innerHTML = '<div class="text-muted small">Could not load schedule at this moment.</div>';
            });
    }

    function renderTimeline() {
        const events = activeScheduleData.events || [];
        const bookings = activeScheduleData.bookings || [];

        if (events.length === 0 && bookings.length === 0) {
            slotStatusIndicator.textContent = 'All Slots Open';
            slotStatusIndicator.className = 'badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 small';
            timelineListEl.innerHTML = '<div class="p-2 bg-success bg-opacity-10 text-success rounded-3 small fw-semibold">' +
                '🟢 All hours available on this date! (Daytime 6am-6pm: ₱50/hr &bull; Nighttime 6pm-11pm: ₱100/hr)' +
            '</div>';
            return;
        }

        slotStatusIndicator.textContent = 'Partially Booked';
        slotStatusIndicator.className = 'badge bg-warning bg-opacity-10 text-warning-emphasis rounded-pill px-3 py-1 small';

        let html = '';

        // Render Events
        events.forEach(function (ev) {
            html += '<div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">' +
                '<div><strong>🔴 ' + format12(ev.start_time) + ' – ' + format12(ev.end_time) + '</strong>: Barangay Event — ' + ev.title + '</div>' +
                '<span class="badge bg-danger text-white rounded-pill px-2 py-1">Reserved</span>' +
            '</div>';
        });

        // Render Bookings
        bookings.forEach(function (b) {
            html += '<div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-warning bg-opacity-10 text-dark border border-warning border-opacity-50">' +
                '<div><strong>🟡 ' + format12(b.start_time) + ' – ' + format12(b.end_time) + '</strong>: Resident Reservation (' + (b.purpose || 'Private Booking') + ')</div>' +
                '<span class="badge bg-secondary text-white rounded-pill px-2 py-1">Reserved</span>' +
            '</div>';
        });

        html += '<div class="text-muted small mt-1 ps-1">🟢 Other unlisted hours are open for booking!</div>';
        timelineListEl.innerHTML = html;
    }

    // 👉 CHECK OVERLAPPING CONFLICT IN REAL TIME
    function checkConflict() {
        const sTime = startTimeInput.value;
        const eTime = endTimeInput.value;

        if (!sTime || !eTime) return;

        let hasConflict = false;
        let conflictReason = '';

        const allReserved = [
            ...(activeScheduleData.events || []).map(e => ({ ...e, type: 'Event', name: e.title })),
            ...(activeScheduleData.bookings || []).map(b => ({ ...b, type: 'Booking', name: b.purpose || 'Resident Reservation' }))
        ];

        for (let r of allReserved) {
            const rStart = (r.start_time || '').slice(0, 5);
            const rEnd = (r.end_time || '').slice(0, 5);

            // Overlap condition: (sTime < rEnd) && (eTime > rStart)
            if (sTime < rEnd && eTime > rStart) {
                hasConflict = true;
                conflictReason = r.type === 'Event' ? 'Barangay Event (' + r.name + ')' : 'Resident Booking (' + r.name + ')';
                conflictReason += ' from ' + format12(rStart) + ' to ' + format12(rEnd);
                break;
            }
        }

        if (hasConflict) {
            conflictAlert.innerHTML = '<strong>⚠️ Time Conflict:</strong> The court is already reserved by <u>' + conflictReason + '</u>. Please choose another time slot above.';
            conflictAlert.style.display = 'block';
            submitBtn.disabled = true;
        } else {
            conflictAlert.style.display = 'none';
            submitBtn.disabled = false;
        }
    }

    // 👉 DYNAMIC DAYTIME (₱50) VS NIGHTTIME (₱100) VS WHOLE DAY (₱1,000) COMPUTATION
    function calculateBooking() {
        let days = 1;
        if (startDateInput.value && endDateInput.value) {
            const sDate = new Date(startDateInput.value);
            const eDate = new Date(endDateInput.value);
            days = Math.max(Math.floor((eDate - sDate) / (1000 * 60 * 60 * 24)) + 1, 1);
        }

        let hoursPerDay = 1;
        let dayHours = 0;
        let nightHours = 0;

        if (startTimeInput.value && endTimeInput.value) {
            const [sh, sm] = startTimeInput.value.split(':').map(Number);
            const [eh, em] = endTimeInput.value.split(':').map(Number);
            let startMin = sh * 60 + sm;
            let endMin = eh * 60 + em;

            if (endMin <= startMin) endMin = startMin + 60; // Fallback 1 hr
            hoursPerDay = (endMin - startMin) / 60;

            let curr = startMin;
            while (curr < endMin) {
                let next = Math.min(curr + 60, endMin);
                let durationHr = (next - curr) / 60;
                let hourOfDay = Math.floor(curr / 60);

                // 6am to 6pm (06:00 - 18:00) = Daytime (@ ₱50/hr)
                if (hourOfDay >= 6 && hourOfDay < 18) {
                    dayHours += durationHr;
                } else {
                    // Before 6am or after 6pm = Nighttime w/ lights (@ ₱100/hr)
                    nightHours += durationHr;
                }
                curr = next;
            }
        }

        const selectedOpt = facilitySelect.selectedOptions[0];
        const facilityName = selectedOpt ? (selectedOpt.dataset.name || selectedOpt.text.split('—')[0].trim()) : 'Covered Court';

        let isWholeDay = hoursPerDay >= 8;
        let subtotal = 0;
        let rateText = '';
        let durationDesc = '';

        if (isWholeDay) {
            subtotal = days * 1000.00;
            rateText = 'Whole Day Package (₱1,000/day)';
            durationDesc = days + (days === 1 ? ' day (Whole Day)' : ' days (Whole Day)');
            rateTypeBadge.textContent = 'Whole Day Package';
            rateTypeBadge.className = 'badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 small';
        } else {
            let dayCost = dayHours * 50.00;
            let nightCost = nightHours * 100.00;
            subtotal = days * (dayCost + nightCost);

            let parts = [];
            if (dayHours > 0) parts.push(dayHours.toFixed(1) + ' hr(s) Daytime (@₱50)');
            if (nightHours > 0) parts.push(nightHours.toFixed(1) + ' hr(s) Nighttime (@₱100)');
            rateText = parts.join(' + ');
            durationDesc = hoursPerDay.toFixed(1) + ' hr(s)/day &bull; ' + days + (days === 1 ? ' day' : ' days');

            rateTypeBadge.textContent = nightHours > 0 ? 'Day & Night Rate' : 'Daytime Hourly Rate';
            rateTypeBadge.className = 'badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 small';
        }

        facilityNameEl.textContent = facilityName;
        rateAppliedEl.innerHTML = rateText;
        durationEl.innerHTML = durationDesc;

        const subtotalFormatted = '₱' + subtotal.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        subtotalEl.textContent = subtotalFormatted;
        baseSubtotalEl.textContent = subtotalFormatted;

        const cashlessSelected = isCashless();
        if (cashlessSelected) {
            cashlessRow.style.display = 'table-row';
        } else {
            cashlessRow.style.display = 'none';
        }

        const grandTotal = subtotal + (cashlessSelected ? cashlessFee : 0);
        totalEl.textContent = '₱' + grandTotal.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    startDateInput.addEventListener('change', function () {
        if (endDateInput.value < startDateInput.value) {
            endDateInput.value = startDateInput.value;
        }
        endDateInput.min = startDateInput.value;
        fetchSchedule();
        calculateBooking();
    });

    endDateInput.addEventListener('change', calculateBooking);
    startTimeInput.addEventListener('change', function () {
        checkConflict();
        calculateBooking();
    });
    endTimeInput.addEventListener('change', function () {
        checkConflict();
        calculateBooking();
    });
    facilitySelect.addEventListener('change', function () {
        fetchSchedule();
        calculateBooking();
    });
    paymentRadios.forEach(r => r.addEventListener('change', calculateBooking));

    // Initial load
    fetchSchedule();
    calculateBooking();
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