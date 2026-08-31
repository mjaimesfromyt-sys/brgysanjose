@extends('layouts.app')
@section('title', 'Request a Document')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="mb-4">
            <a href="{{ route('requests.index') }}" class="text-decoration-none small fw-semibold">&larr; My requests</a>
            <h1 class="page-title mt-2">Request a Document</h1>
            <p class="page-subtitle">
                Submit online, then claim in person at the barangay hall using your claim code.
            </p>
        </div>

        <div class="card-soft p-4 mb-4">
            <form method="POST" action="{{ route('requests.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="transaction_type_id" class="form-label">Document / Transaction</label>
                    <select id="transaction_type_id" name="transaction_type_id"
                            class="form-select @error('transaction_type_id') is-invalid @enderror" required>
                        <option value="">— Select —</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}"
                                data-residency="{{ $type->requires_residency ? '1' : '0' }}"
                                data-fee="{{ $type->fee ?? 0 }}"
                                {{ old('transaction_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}{{ $type->requires_residency ? ' (residents only)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('transaction_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label for="purpose" class="form-label">
                        Purpose <span class="text-muted fw-normal">(optional)</span>
                    </label>
                    <input id="purpose" type="text" name="purpose" value="{{ old('purpose') }}"
                           placeholder="e.g. for employment, for scholarship" class="form-control">
                </div>

                <div class="d-flex justify-content-between align-items-center p-3 mb-3" style="background:#f5f7f5;border-radius:.5rem">
                    <span class="fw-semibold">Total payable</span>
                    <span class="fw-bold fs-5" id="totalPayable">₱0.00</span>
                </div>

                @include('partials.payment-method')

                <button class="btn btn-primary btn-lg w-100">Submit request</button>
            </form>
        </div>

        <div class="card-soft p-4">
            <h2 class="h6 fw-bold mb-1">What to bring to the barangay hall</h2>
            <p class="text-muted mb-3">
                Once validated you'll get a claim code. Bring the code plus these documents.
            </p>

            @foreach ($types as $type)
                <div class="requirements-block" data-type="{{ $type->id }}" style="display:none;">
                    <ul class="list-unstyled m-0">
                        @forelse ($type->requirements as $req)
                            <li class="d-flex gap-2 py-2 border-bottom">
                                <span class="text-success flex-shrink-0" aria-hidden="true">&check;</span>
                                <span>{{ $req->item }}</span>
                            </li>
                        @empty
                            <li class="text-muted py-2">No specific requirements listed.</li>
                        @endforelse
                    </ul>

                    @if (! is_null($type->fee) && $type->fee > 0)
                        <p class="mt-3 mb-0">
                            <strong>Fee:</strong> ₱{{ number_format($type->fee, 2) }}
                            <span class="text-muted">(payable at the hall)</span>
                        </p>
                    @endif
                </div>
            @endforeach

            <p class="text-muted mb-0" id="requirementsHint">
                Select a document above to see its requirements.
            </p>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById('transaction_type_id');
        const hint   = document.getElementById('requirementsHint');
        if (!select) return;

        function showRequirements() {
            document.querySelectorAll('.requirements-block').forEach(function (b) {
                b.style.display = 'none';
            });
            const id = select.value;
            let shown = false;
            if (id) {
                const block = document.querySelector('.requirements-block[data-type="' + id + '"]');
                if (block) {
                    block.style.display = 'block';
                    shown = true;
                }
            }
            if (hint) hint.style.display = shown ? 'none' : 'block';
        }

        select.addEventListener('change', showRequirements);
        showRequirements();

        const total = document.getElementById('totalPayable');
        if (total) {
            const transactionFee = {{ \App\Services\PayMongoService::transactionFee() }};

            function isCashless() {
                const selected = document.querySelector('.payment-method-radio:checked');
                return !!selected && selected.value !== 'cash';
            }

            function recalcTotal() {
                const option = select.options[select.selectedIndex];
                const fee = option ? (parseFloat(option.dataset.fee) || 0) : 0;
                const sum = fee + (isCashless() ? transactionFee : 0);
                total.textContent = '₱' + sum.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
            select.addEventListener('change', recalcTotal);
            document.querySelectorAll('.payment-method-radio').forEach(function (radio) {
                radio.addEventListener('change', recalcTotal);
            });
            recalcTotal();
        }
    });
</script>
@endpush
