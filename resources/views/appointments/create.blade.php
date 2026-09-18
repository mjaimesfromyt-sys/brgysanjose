@extends('layouts.app')

@section('title', 'Book Captain\'s Appointment')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-dark">
                        @include('partials.icon', ['name' => 'calendar', 'size' => 20]) Book an Appointment with Punong Barangay
                    </h5>
                    <small class="text-muted">Schedule a formal meeting, mediation session (pagpa-husay), or legal consultation.</small>
                </div>
                <div class="card-body p-4">

                    @if($errors->any())
                        <div class="alert alert-danger border-0 shadow-sm rounded-3">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('appointments.store') }}" method="POST" id="appointmentForm">
                        @csrf

                        <!-- 1. Appointment Date -->
                        <div class="mb-4">
                            <label for="appointment_date" class="form-label fw-bold">Date of Appointment <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-lg" id="appointment_date" name="appointment_date" 
                                   value="{{ old('appointment_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required>
                        </div>

                        <!-- 2. Interactive Schedule & Conflict Box -->
                        <div class="card bg-light border-0 rounded-3 mb-4 p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-secondary" id="scheduleDateLabel">
                                    📅 Captain's Schedule & Slot Availability on: <span class="text-success" id="displayDate">{{ date('Y-m-d') }}</span>
                                </span>
                                <span class="badge bg-success" id="dayStatusBadge">Available</span>
                            </div>

                            <div id="slotsContainer" class="d-flex flex-column gap-2 mb-2">
                                <!-- Dynamic slots load via JS -->
                            </div>

                            <div class="text-muted small">
                                🟢 Office hours (8:00 AM – 5:00 PM). Appointments are arranged in order of confirmation.
                            </div>
                        </div>

                        <!-- 3. Dynamic Conflict Warning Banner -->
                        <div class="alert alert-danger d-none align-items-center mb-4 border-0 shadow-sm" id="conflictAlert" role="alert">
                            <div>
                                ⚠️ <strong>Time Conflict:</strong> <span id="conflictMessage">The chosen time slot overlaps with Kapitan's blocked schedule. Please pick another time.</span>
                            </div>
                        </div>

                        <!-- 4. Time Selection -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="start_time" class="form-label fw-bold">Start Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control form-control-lg" id="start_time" name="start_time" min="08:00" max="16:00" 
                                       value="{{ old('start_time', '09:00') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="end_time" class="form-label fw-bold">End Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control form-control-lg" id="end_time" name="end_time" 
                                       value="{{ old('end_time', '10:00') }}" required>
                            </div>
                        </div>

                        <!-- 5. Category -->
                        <div class="mb-4">
                            <label for="category" class="form-label fw-bold">Purpose / Category <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg" id="category" name="category" required>
                                <option value="" disabled {{ old('category') ? '' : 'selected' }}>Select consultation category</option>
                                <option value="Mediation (Pagpa-husay / Lupon)" {{ old('category') == 'Mediation (Pagpa-husay / Lupon)' ? 'selected' : '' }}>Mediation (Pagpa-husay / Lupon)</option>
                                <option value="Official Barangay Consultation" {{ old('category') == 'Official Barangay Consultation' ? 'selected' : '' }}>Official Barangay Consultation</option>
                                <option value="Boundary / Neighborhood Concern" {{ old('category') == 'Boundary / Neighborhood Concern' ? 'selected' : '' }}>Boundary / Neighborhood Concern</option>
                                <option value="Legal Assistance / Certification" {{ old('category') == 'Legal Assistance / Certification' ? 'selected' : '' }}>Legal Assistance / Certification</option>
                                <option value="Other Personal Matter" {{ old('category') == 'Other Personal Matter' ? 'selected' : '' }}>Other Personal Matter</option>
                            </select>
                        </div>

                        <!-- 6. Detailed Reason -->
                        <div class="mb-4">
                            <label for="reason" class="form-label fw-bold">Detailed Reason for Appointment <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="reason" name="reason" rows="4" 
                                      placeholder="Provide context regarding the issue or matter to discuss with Kapitan..." required>{{ old('reason') }}</textarea>
                            <div class="form-text">This will be treated with 100% confidentiality by the Barangay Secretariat.</div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2">
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary px-5 btn-lg" id="submitBtn">Submit Appointment Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dateInput = document.getElementById('appointment_date');
    const displayDate = document.getElementById('displayDate');
    const dayStatusBadge = document.getElementById('dayStatusBadge');
    const slotsContainer = document.getElementById('slotsContainer');
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    const conflictAlert = document.getElementById('conflictAlert');
    const conflictMessage = document.getElementById('conflictMessage');
    const submitBtn = document.getElementById('submitBtn');

    let currentSlots = [];

    function fetchSlots(date) {
        displayDate.textContent = date;
        slotsContainer.innerHTML = '<div class="text-muted small py-2">Loading schedule...</div>';

        fetch(`{{ route('appointments.slots') }}?date=${date}`)
            .then(res => res.json())
            .then(data => {
                currentSlots = data.slots;
                dayStatusBadge.textContent = data.status;
                dayStatusBadge.className = data.status === 'Available' ? 'badge bg-success' : 'badge bg-warning text-dark';

                renderSlots(data.slots);
                validateConflict();
            })
            .catch(() => {
                slotsContainer.innerHTML = '<div class="text-danger small py-2">Failed to load schedule.</div>';
            });
    }

    function renderSlots(slots) {
        if (!slots || slots.length === 0) {
            slotsContainer.innerHTML = '<div class="p-2 rounded bg-white border border-success-subtle small text-success">🟢 No booked appointments or official travels recorded on this date. Full day open!</div>';
            return;
        }

        let html = '';
        slots.forEach(slot => {
            const isDanger = slot.color === 'danger';
            const bgClass = isDanger ? 'bg-danger-subtle text-danger border-danger-subtle' : 'bg-warning-subtle text-dark border-warning-subtle';
            const badgeClass = isDanger ? 'bg-danger text-white' : 'bg-dark text-white';
            const icon = isDanger ? '🔴' : '🟡';

            html += `
                <div class="d-flex justify-content-between align-items-center p-2 rounded border ${bgClass} small">
                    <div>
                        <strong>${icon} ${slot.start_time} – ${slot.end_time}:</strong> ${slot.title}
                    </div>
                    <span class="badge ${badgeClass}">${slot.badge}</span>
                </div>
            `;
        });
        slotsContainer.innerHTML = html;
    }

    function timeToMinutes(timeStr) {
        const parts = timeStr.split(':').map(Number);
        return parts[0] * 60 + parts[1];
    }

    function validateConflict() {
        const startVal = startTimeInput.value;
        const endVal = endTimeInput.value;

        conflictAlert.classList.add('d-none');
        submitBtn.disabled = false;

        if (!startVal || !endVal) return;

        const reqStart = timeToMinutes(startVal);
        const reqEnd = timeToMinutes(endVal);

        if (reqEnd <= reqStart) {
            conflictMessage.textContent = 'End time must be later than start time.';
            conflictAlert.classList.remove('d-none');
            submitBtn.disabled = true;
            return;
        }

        for (const slot of currentSlots) {
            const slotStart = timeToMinutes(slot.start_raw);
            const slotEnd = timeToMinutes(slot.end_raw);

            if (reqStart < slotEnd && reqEnd > slotStart) {
                conflictMessage.textContent = `Schedule overlap detected with: "${slot.title}" (${slot.start_time} - ${slot.end_time}). Please choose another time.`;
                conflictAlert.classList.remove('d-none');
                submitBtn.disabled = true;
                return;
            }
        }
    }

    dateInput.addEventListener('change', function () {
        fetchSlots(this.value);
    });

    startTimeInput.addEventListener('input', validateConflict);
    endTimeInput.addEventListener('input', validateConflict);

    fetchSlots(dateInput.value);
});
</script>
@endsection