{{-- Cash / Cashless selector, shared by the equipment rental, booking and document request forms.
     Choosing a cashless method redirects the resident to a real PayMongo checkout page after submit. --}}
<div class="mb-4">
    <label class="form-label d-block">How will you pay?</label>

    <div class="form-check mb-2">
        <input class="form-check-input payment-method-radio" type="radio" name="payment_method" id="payment_cash" value="cash"
               {{ old('payment_method', 'cash') === 'cash' ? 'checked' : '' }}>
        <label class="form-check-label" for="payment_cash">Cash (pay at the barangay hall)</label>
    </div>

    <div class="ps-1">
        <div class="text-muted small text-uppercase fw-semibold mb-1" style="letter-spacing:.05em">Cashless</div>
        <div class="d-flex gap-3 flex-wrap">
            <div class="form-check">
                <input class="form-check-input payment-method-radio" type="radio" name="payment_method" id="payment_gcash" value="gcash"
                       {{ old('payment_method') === 'gcash' ? 'checked' : '' }}>
                <label class="form-check-label" for="payment_gcash">GCash</label>
            </div>
            <div class="form-check">
                <input class="form-check-input payment-method-radio" type="radio" name="payment_method" id="payment_paymaya" value="paymaya"
                       {{ old('payment_method') === 'paymaya' ? 'checked' : '' }}>
                <label class="form-check-label" for="payment_paymaya">PayMaya</label>
            </div>
            <div class="form-check">
                <input class="form-check-input payment-method-radio" type="radio" name="payment_method" id="payment_bank" value="bank_transfer"
                       {{ old('payment_method') === 'bank_transfer' ? 'checked' : '' }}>
                <label class="form-check-label" for="payment_bank">Bank Transfer</label>
            </div>
        </div>
    </div>
    @error('payment_method') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

    <p id="paymentRedirectHint" class="text-muted small mt-2 mb-0" style="display:none;">
        You'll be taken to a secure payment page to complete this. A ₱{{ number_format(\App\Services\PayMongoService::transactionFee(), 2) }} transaction fee applies to cashless payments.
        Once paid, you're brought back here with your claim code and receipt.
    </p>
</div>

@push('scripts')
<script>
    (function () {
        const radios = document.querySelectorAll('.payment-method-radio');
        const hint   = document.getElementById('paymentRedirectHint');
        if (!radios.length || !hint) return;

        function sync() {
            const selected = document.querySelector('.payment-method-radio:checked');
            hint.style.display = (!!selected && selected.value !== 'cash') ? 'block' : 'none';
        }

        radios.forEach(function (radio) {
            radio.addEventListener('change', sync);
        });
        sync();
    })();
</script>
@endpush
