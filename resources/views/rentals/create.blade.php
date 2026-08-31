@extends('layouts.app')
@section('title', 'Rent Equipment')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">

        <div class="mb-4">
            <a href="{{ route('rentals.index') }}" class="text-decoration-none small fw-semibold">&larr; My rentals</a>
            <h1 class="page-title mt-2">Rent Equipment</h1>
            <p class="page-subtitle">
                Request chairs, tables, tents or other barangay equipment.
                Your request goes to the barangay for approval.
            </p>
        </div>

        @if ($equipment->isEmpty())
            <div class="card-soft">
                <div class="empty">
                    <div class="empty__title">No equipment available</div>
                    <p class="mb-0">The barangay has not listed any rentable equipment yet. Please check back later.</p>
                </div>
            </div>
        @else
            <div class="card-soft p-4">
                <form method="POST" action="{{ route('rentals.store') }}">
                    @csrf

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

                    <h2 class="h6 fw-bold text-muted mb-3 mt-2" style="text-transform:uppercase;letter-spacing:.07em">
                        What you need
                    </h2>

                    @error('items') <div class="alert alert-danger py-2">{{ $message }}</div> @enderror

                    <div class="table-responsive mb-3">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th class="text-end" style="width:8rem">Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($equipment as $index => $item)
                                    <tr>
                                        <td>
                                            <input type="hidden" name="items[{{ $index }}][equipment_id]" value="{{ $item->id }}">
                                            <div class="fw-semibold">{{ $item->name }}</div>
                                            @if ($item->description)
                                                <div class="text-muted small">{{ $item->description }}</div>
                                            @endif
                                            <div class="text-muted small">
                                                {{ $item->total_stock }} in barangay stock
                                                @if ($item->fee)
                                                    &middot; ₱{{ number_format($item->fee, 2) }} each
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-end align-middle">
                                            <input type="number" name="items[{{ $index }}][quantity]" min="0"
                                                   max="{{ $item->total_stock }}"
                                                   data-fee="{{ $item->fee ?? 0 }}"
                                                   value="{{ old('items.'.$index.'.quantity', 0) }}"
                                                   class="form-control form-control-sm text-end rental-qty @error('items.'.$index.'.quantity') is-invalid @enderror">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mb-4">
                        <label for="purpose" class="form-label">Purpose</label>
                        <input id="purpose" type="text" name="purpose"
                               value="{{ old('purpose') }}" placeholder="e.g. Barangay fiesta, family reunion"
                               class="form-control @error('purpose') is-invalid @enderror" required>
                        @error('purpose') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-3 mb-3" style="background:#f5f7f5;border-radius:.5rem">
                        <span class="fw-semibold">Total payable</span>
                        <span class="fw-bold fs-5" id="totalPayable">₱0.00</span>
                    </div>

                    @include('partials.payment-method')

                    <button class="btn btn-primary btn-lg w-100">Submit rental request</button>
                </form>
            </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('start_date')?.addEventListener('change', function () {
        const end = document.getElementById('end_date');
        end.min = this.value;
        if (! end.value || end.value < this.value) {
            end.value = this.value;
        }
    });

    (function () {
        const qtyInputs = document.querySelectorAll('.rental-qty');
        const total = document.getElementById('totalPayable');
        if (!qtyInputs.length || !total) return;

        const transactionFee = {{ \App\Services\PayMongoService::transactionFee() }};

        function isCashless() {
            const selected = document.querySelector('.payment-method-radio:checked');
            return !!selected && selected.value !== 'cash';
        }

        function recalc() {
            let sum = 0;
            qtyInputs.forEach(function (input) {
                const fee = parseFloat(input.dataset.fee) || 0;
                const qty = parseInt(input.value, 10) || 0;
                sum += fee * qty;
            });
            if (isCashless()) sum += transactionFee;
            total.textContent = '₱' + sum.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        qtyInputs.forEach(function (input) {
            input.addEventListener('input', recalc);
        });
        document.querySelectorAll('.payment-method-radio').forEach(function (radio) {
            radio.addEventListener('change', recalc);
        });
        recalc();
    })();
</script>
@endpush
