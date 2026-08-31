@extends('layouts.app')
@section('title', 'Book a Facility')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">

        <div class="mb-4">
            <a href="{{ route('bookings.index') }}" class="text-decoration-none small fw-semibold">&larr; My bookings</a>
            <h1 class="page-title mt-2">Book a Facility</h1>
            <p class="page-subtitle">
                Your request goes to the barangay for approval. You'll see the result under My Bookings.
            </p>
        </div>

        <div class="card-soft p-4 p-md-4">
            <form method="POST" action="{{ route('bookings.store') }}">
                @csrf

                <div class="mb-4">
                    <label for="facility_id" class="form-label">Facility</label>
                    <select id="facility_id" name="facility_id"
                            class="form-select @error('facility_id') is-invalid @enderror" required>
                        <option value="">— Select a facility —</option>
                        @foreach ($facilities as $facility)
                            <option value="{{ $facility->id }}" data-fee="{{ $facility->fee ?? 0 }}"
                                {{ old('facility_id') == $facility->id ? 'selected' : '' }}>
                                {{ $facility->name }}@if($facility->capacity) (capacity {{ $facility->capacity }})@endif
                                @if ($facility->fee) — ₱{{ number_format($facility->fee, 2) }} @endif
                            </option>
                        @endforeach
                    </select>
                    @error('facility_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <h2 class="h6 fw-bold text-muted mb-3" style="text-transform:uppercase;letter-spacing:.07em">
                    When
                </h2>

                <div class="row">
                    <div class="col-sm-6 mb-3">
                        <label for="start_date" class="form-label">Start date</label>
                        <input id="start_date" type="date" name="start_date"
                               value="{{ old('start_date') }}" min="{{ date('Y-m-d') }}"
                               class="form-control @error('start_date') is-invalid @enderror" required>
                        @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-sm-6 mb-3">
                        <label for="end_date" class="form-label">End date</label>
                        <input id="end_date" type="date" name="end_date"
                               value="{{ old('end_date') }}" min="{{ date('Y-m-d') }}"
                               class="form-control @error('end_date') is-invalid @enderror" required>
                        @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="alert alert-info py-2 mb-3">
                    For multi-day bookings, the time below applies to <strong>each day</strong> in the range.
                </div>

                <div class="row">
                    <div class="col-sm-6 mb-3">
                        <label for="start_time" class="form-label">Start time</label>
                        <input id="start_time" type="time" name="start_time"
                               value="{{ old('start_time') }}"
                               class="form-control @error('start_time') is-invalid @enderror" required>
                        @error('start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-sm-6 mb-3">
                        <label for="end_time" class="form-label">End time</label>
                        <input id="end_time" type="time" name="end_time"
                               value="{{ old('end_time') }}"
                               class="form-control @error('end_time') is-invalid @enderror" required>
                        @error('end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="purpose" class="form-label">Purpose</label>
                    <input id="purpose" type="text" name="purpose"
                           value="{{ old('purpose') }}" placeholder="e.g. Birthday celebration, meeting"
                           class="form-control @error('purpose') is-invalid @enderror" required>
                    @error('purpose') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center p-3 mb-3" style="background:#f5f7f5;border-radius:.5rem">
                    <span class="fw-semibold">Total payable</span>
                    <span class="fw-bold fs-5" id="totalPayable">₱0.00</span>
                </div>

                @include('partials.payment-method')

                <button class="btn btn-primary btn-lg w-100">Submit booking request</button>
            </form>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('start_date').addEventListener('change', function () {
        const end = document.getElementById('end_date');
        end.min = this.value;
        if (! end.value || end.value < this.value) {
            end.value = this.value;
        }
    });

    (function () {
        const select = document.getElementById('facility_id');
        const total = document.getElementById('totalPayable');
        if (!select || !total) return;

        const transactionFee = {{ \App\Services\PayMongoService::transactionFee() }};

        function isCashless() {
            const selected = document.querySelector('.payment-method-radio:checked');
            return !!selected && selected.value !== 'cash';
        }

        function recalc() {
            const option = select.options[select.selectedIndex];
            const fee = option ? (parseFloat(option.dataset.fee) || 0) : 0;
            const sum = fee + (isCashless() ? transactionFee : 0);
            total.textContent = '₱' + sum.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        select.addEventListener('change', recalc);
        document.querySelectorAll('.payment-method-radio').forEach(function (radio) {
            radio.addEventListener('change', recalc);
        });
        recalc();
    })();
</script>
@endpush
