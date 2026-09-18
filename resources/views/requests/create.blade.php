@extends('layouts.app')

@section('title', 'Request Document — Barangay San Jose')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">

            {{-- Hero Header --}}
            <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 52px; height: 52px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="#198754" viewBox="0 0 16 16">
                            <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/>
                            <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0 text-dark">Request Barangay Document</h3>
                        <p class="text-muted small mb-0">Follow the 5 simple steps below to request official certificates, clearances, and permits.</p>
                    </div>
                </div>
                <a href="{{ route('requests.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    &larr; My Requests
                </a>
            </div>

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                    <strong>Notice:</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('requests.store') }}" method="POST" id="docRequestForm">
                @csrf

                {{-- 👉 STEP 1: CHOOSE DOCUMENT --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-dark mb-3">1. Choose Document / Certification</h5>

                        {{-- Search Filter Box --}}
                        <label class="form-label small fw-semibold text-secondary">Search or Filter Document</label>
                        <div class="input-group mb-2 shadow-sm rounded-3 overflow-hidden">
                            <span class="input-group-text bg-white border-end-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#198754" viewBox="0 0 16 16">
                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                </svg>
                            </span>
                            <input type="text" id="docSearchInput" class="form-control border-start-0 py-2 ps-1" placeholder="Type here to search (e.g. Business, Clearance, Indigency, Jobseeker, Residency, Motor Loan)...">
                        </div>

                        {{-- Live Search Match Cards --}}
                        <div id="liveSearchResults" class="list-group mb-3 shadow-sm rounded-3" style="display: none; max-height: 220px; overflow-y: auto;"></div>

                        <label class="form-label small fw-semibold text-secondary mt-2">Or Select from Official Catalog <span class="text-danger">*</span></label>
                        <select name="transaction_type_id" id="transaction_type_id" class="form-select form-select-lg rounded-3 @error('transaction_type_id') is-invalid @enderror" required>
                            <option value="" disabled selected>-- Select an Official Barangay Document --</option>
                            @foreach($types as $type)
                                @php
                                    $reqList = [];
                                    if (is_iterable($type->requirements)) {
                                        foreach ($type->requirements as $r) {
                                            if (is_object($r) && isset($r->item)) {
                                                $reqList[] = $r->item;
                                            } elseif (is_array($r) && isset($r['item'])) {
                                                $reqList[] = $r['item'];
                                            } elseif (is_string($r)) {
                                                $reqList[] = $r;
                                            }
                                        }
                                    }
                                    $reqString = implode('||', array_filter($reqList));
                                @endphp
                                <option value="{{ $type->id }}" 
                                    data-name="{{ $type->name }}"
                                    data-fee="{{ $type->fee ?? 0 }}"
                                    data-requirements="{{ $reqString }}"
                    data-slug="{{ $type->slug ?? '' }}"
                                    {{ old('transaction_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} — ₱{{ number_format($type->fee ?? 0, 2) }}
                                </option>
                            @endforeach
                        </select>
                        @error('transaction_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        {{-- Conditional Business Information Input --}}
                        <div id="businessFieldsBox" class="mt-4 p-3 rounded-3 bg-light border border-success border-opacity-25" style="display: none;">
                            <h6 class="fw-bold text-success mb-2">Business Clearance Information</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-secondary">Business Registered Name <span class="text-danger">*</span></label>
                                    <input type="text" name="business_name" id="business_name" class="form-control rounded-3" placeholder="e.g., RJ General Merchandise, San Jose Bakery" value="{{ old('business_name') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-secondary">Nature / Line of Business <span class="text-danger">*</span></label>
                                    <input type="text" name="business_nature" id="business_nature" class="form-control rounded-3" placeholder="e.g., Retail Store, Bakeshop, Crafts & Rentals" value="{{ old('business_nature') }}">
                                </div>
                            <div class="row g-3 mt-1">
                                <div class="col-md-12">
                                    <label class="form-label small fw-semibold text-secondary">Business Address (Purok) <span class="text-danger">*</span></label>
                                    <input type="text" name="business_address" id="business_address" class="form-control rounded-3" placeholder="e.g., Purok 6, San Jose, Talibon, Bohol" value="{{ old('business_address') }}">
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 👉 STEP 2: REQUIREMENTS & WHAT TO BRING --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="fw-bold text-dark mb-0">2. Requirements & What to Bring</h5>
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 small" id="officialFeeBadge">
                                Official Rate: ₱0.00
                            </span>
                        </div>
                        <p class="text-muted small mb-3">Please prepare and present the following required documents upon claiming at Barangay San Jose Hall:</p>

                        <div id="requirementsListWrap" class="p-3 bg-light rounded-3">
                            <div id="requirementsEmptyNotice" class="text-muted small text-center py-2">
                                Please select a document in Step 1 to view specific requirements.
                            </div>
                            <ul id="requirementsUl" class="list-unstyled mb-0 d-flex flex-column gap-2" style="display: none;"></ul>
                        </div>
                    </div>
                </div>

                {{-- 👉 STEP 3: PURPOSE OF REQUEST & PROOF OF INCOME (MOTOR LOAN) --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-dark mb-3">3. Purpose of Request</h5>
                        
                        <label class="form-label small fw-semibold text-secondary">Specific Purpose / Reason <span class="text-danger">*</span></label>
                        <input type="text" name="purpose" id="purpose_input" class="form-control rounded-3 @error('purpose') is-invalid @enderror" 
                            placeholder="e.g., Motorcycle Loan Application, Bank Account Opening, Scholarship, Employment..." 
                            value="{{ old('purpose') }}" required>
                        @error('purpose')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        {{-- 👉 PROOF OF OCCUPATION & MONTHLY INCOME (PARA SA MOTOR LOAN / FINANCING) --}}
                        <div class="mt-3 p-3 bg-light rounded-3 border border-success border-opacity-25">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_income_toggle" name="include_income" value="1" {{ old('include_income') ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-dark small cursor-pointer" for="include_income_toggle">
                                    Include Proof of Occupation & Monthly Income? (e.g. for Motorcycle Loan, Appliance Loan, Bank Financing)
                                </label>
                            </div>

                            <div id="incomeFieldsBox" class="mt-3" style="display: none;">
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <label class="form-label small fw-semibold text-secondary">Occupation / Work <span class="text-danger">*</span></label>
                                        <input type="text" name="occupation" id="occupation_input" class="form-control rounded-3" placeholder="e.g., Food Attendant, Carpenter, Driver, Vendor" value="{{ old('occupation') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold text-secondary">Monthly Income (₱) <span class="text-danger">*</span></label>
                                        <input type="number" step="100" name="monthly_income" id="monthly_income_input" class="form-control rounded-3" placeholder="e.g., 10000" value="{{ old('monthly_income') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold text-secondary">Engaged Since</label>
                                        <input type="text" name="employed_since" id="employed_since_input" class="form-control rounded-3" placeholder="e.g., February 2026" value="{{ old('employed_since') }}">
                                    </div>
                                </div>
                                <div class="form-text text-muted small mt-2">
                                    This will certify your employment status and monthly earnings on the official certificate (e.g., <em>with a monthly income of Ten Thousand Pesos (P10,000.00)</em>).
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 👉 CERTIFICATE-SPECIFIC FIELDS (dynamic base sa gipiling document) --}}
                <div id="certFieldsWrapper" class="card border-0 shadow-sm rounded-4 mb-4 bg-white" style="display: none;">
                    <div class="card-header bg-light bg-opacity-50 py-3 px-4 border-0">
                        <h6 class="mb-0 fw-bold text-dark">Additional Details</h6>
                        <small class="text-muted">Kinahanglan kini para sa imong gipiling dokumento.</small>
                    </div>
                    <div class="card-body p-4">

                        <div class="cert-group" data-group="subject" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Name of Person the Certificate is About <span class="text-danger">*</span></label>
                                <input type="text" name="subject_name" class="form-control rounded-3" placeholder="e.g., Marcelina Borja Gurrea" value="{{ old('subject_name') }}">
                                <div class="form-text small">Sagdi kung ikaw mismo ang subject.</div>
                            </div>
                        </div>

                        <div class="cert-group" data-group="requester" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Requested By</label>
                                <input type="text" name="requester_name" class="form-control rounded-3" placeholder="e.g., Mary Ann G. Yucot" value="{{ old('requester_name') }}">
                                <div class="form-text small">Sagdi kung ikaw ang nag-request.</div>
                            </div>
                        </div>

                        <div class="cert-group" data-group="land" style="display: none;">
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold text-secondary">Lot No.</label>
                                    <input type="text" name="lot_no" class="form-control rounded-3" placeholder="e.g., 4494" value="{{ old('lot_no') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold text-secondary">Tax Declaration No.</label>
                                    <input type="text" name="tax_dec_no" class="form-control rounded-3" placeholder="e.g., 2018-42-0018-00722" value="{{ old('tax_dec_no') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold text-secondary">CAD No.</label>
                                    <input type="text" name="cad_no" class="form-control rounded-3" placeholder="e.g., 395" value="{{ old('cad_no') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-secondary">Land Area</label>
                                    <input type="text" name="land_area" class="form-control rounded-3" placeholder="e.g., One Thousand (1,000) Sq.M." value="{{ old('land_area') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-secondary">Registered Owner</label>
                                    <input type="text" name="land_owner" class="form-control rounded-3" placeholder="e.g., Trinidad Auxtero" value="{{ old('land_owner') }}">
                                </div>
                            </div>
                        </div>

                        <div class="cert-group" data-group="spouse" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Spouse Name</label>
                                <input type="text" name="owner_spouse" class="form-control rounded-3" placeholder="e.g., Onofre Auxtero" value="{{ old('owner_spouse') }}">
                            </div>
                        </div>

                        <div class="cert-group" data-group="deceased" style="display: none;">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-secondary">Date Died</label>
                                    <input type="date" name="date_died" class="form-control rounded-3" value="{{ old('date_died') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-secondary">Place of Burial / Death</label>
                                    <input type="text" name="burial_place" class="form-control rounded-3" placeholder="e.g., Talibon Municipal Cemetery" value="{{ old('burial_place') }}">
                                </div>
                            </div>
                        </div>

                        <div class="cert-group" data-group="control_no" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Control / ID Number</label>
                                <input type="text" name="control_no" class="form-control rounded-3" placeholder="e.g., 07-1243-000-1203" value="{{ old('control_no') }}">
                            </div>
                        </div>

                        <div class="cert-group" data-group="disability" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Type of Disability</label>
                                <input type="text" name="disability_type" class="form-control rounded-3" placeholder="e.g., Intellectual Disability" value="{{ old('disability_type') }}">
                            </div>
                        </div>

                        <div class="cert-group" data-group="since_year" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Since (Year) / Number of Years</label>
                                <input type="text" name="solo_parent_since" class="form-control rounded-3" placeholder="e.g., 2016 o 13" value="{{ old('solo_parent_since') }}">
                            </div>
                        </div>

                        <div class="cert-group" data-group="parents" style="display: none;">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-secondary">Father's Name</label>
                                    <input type="text" name="father_name" class="form-control rounded-3" placeholder="e.g., Greggy Trago Malisa" value="{{ old('father_name') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-secondary">Mother's Name</label>
                                    <input type="text" name="mother_name" class="form-control rounded-3" placeholder="e.g., Mayflor Castro Malisa" value="{{ old('mother_name') }}">
                                </div>
                            </div>
                        </div>

                        <div class="cert-group" data-group="employer" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Employer / Organizer</label>
                                <input type="text" name="employer_name" class="form-control rounded-3" placeholder="e.g., Shopee Express" value="{{ old('employer_name') }}">
                            </div>
                        </div>

                        <div class="cert-group" data-group="activity_date" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Activity / Service Date</label>
                                <input type="date" name="activity_date" class="form-control rounded-3" value="{{ old('activity_date') }}">
                            </div>
                        </div>

                        <div class="cert-group" data-group="name_list" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">List of Names</label>
                                <textarea name="name_list" rows="4" class="form-control rounded-3" placeholder="Juan Dela Cruz, Maria Santos, Pedro Reyes">{{ old('name_list') }}</textarea>
                                <div class="form-text small">Bulaga ang matag pangalan gamit ang comma (,).</div>
                            </div>
                        </div>

                    </div>
                </div>
                {{-- 👉 STEP 4: REQUEST CHECKOUT SUMMARY --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                    <div class="card-header bg-light bg-opacity-50 py-3 px-4 border-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-dark mb-0">4. Request Checkout Summary</h5>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 small">
                            Fee Computation
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <div id="checkoutDocEmpty" class="text-center py-4 text-muted">
                            <span class="fw-semibold">No document selected yet.</span>
                            <div class="small">Choose a document above in Step 1 to preview fee breakdown.</div>
                        </div>

                        <div id="checkoutDocTableWrap" style="display: none;">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0" style="border-color: #e2e8f0;">
                                    <thead style="background-color: #f8fafc;">
                                        <tr class="text-secondary small text-uppercase">
                                            <th class="ps-3 py-2">Document / Certification</th>
                                            <th class="py-2 text-center" style="width: 160px;">Issuance Type</th>
                                            <th class="pe-3 py-2 text-end" style="width: 150px;">Fee</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="ps-3 py-3 fw-bold text-dark" id="checkoutDocName">—</td>
                                            <td class="text-center py-3 text-muted small">Standard Issuance</td>
                                            <td class="pe-3 py-3 text-end fw-bold text-dark" id="checkoutDocFee">₱0.00</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-light">
                                            <td colspan="2" class="text-end fw-semibold text-secondary py-2">Document Subtotal:</td>
                                            <td class="text-end fw-bold text-dark pe-3 py-2" id="checkoutDocSubtotal">₱0.00</td>
                                        </tr>
                                        <tr id="checkoutCashlessRow" class="table-light" style="display: none;">
                                            <td colspan="2" class="text-end text-muted small py-1">Cashless Online Processing Fee:</td>
                                            <td class="text-end text-muted small pe-3 py-1">₱10.00</td>
                                        </tr>
                                        <tr style="background-color: #f0fdf4; border-top: 2px solid #16a34a;">
                                            <td colspan="2" class="text-end fw-bold fs-6 text-success py-3">Total Amount to Pay:</td>
                                            <td class="text-end pe-3 py-3">
                                                <span class="fw-bold fs-4 text-success" id="checkoutDocTotal">₱0.00</span>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 👉 STEP 5: PREFERRED PAYMENT METHOD --}}
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
                                            <div class="small text-muted">Pay directly to the Barangay Treasurer upon claiming.</div>
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
                                instantly, so you only visit the barangay hall to claim your document.
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
                        Submit Document Request &rarr;
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const docSelect = document.getElementById('transaction_type_id');

    // 👉 DYNAMIC CERTIFICATE FIELDS
    const certFieldMap = @json($fieldMap ?? []);
    const certWrapper = document.getElementById('certFieldsWrapper');

    function toggleCertFields() {
        if (!docSelect || !certWrapper) return;

        const opt = docSelect.options[docSelect.selectedIndex];
        const slug = opt ? (opt.getAttribute('data-slug') || '') : '';
        const groups = certFieldMap[slug] || [];

        document.querySelectorAll('.cert-group').forEach(function (el) {
            const g = el.getAttribute('data-group');
            el.style.display = groups.indexOf(g) !== -1 ? 'block' : 'none';
        });

        certWrapper.style.display = groups.length > 0 ? 'block' : 'none';

        const autoIncomeSlugs = @json(config("certificate_fields.income", []));
        const incToggle = document.getElementById("include_income_toggle");
        if (incToggle && autoIncomeSlugs.indexOf(slug) !== -1) {
            incToggle.checked = true;
            incToggle.dispatchEvent(new Event("change"));
        }
    }

    if (docSelect) {
        docSelect.addEventListener('change', toggleCertFields);
        toggleCertFields();
    }
    const searchInput = document.getElementById('docSearchInput');
    const liveResults = document.getElementById('liveSearchResults');
    const businessBox = document.getElementById('businessFieldsBox');
    const bizNameInput = document.getElementById('business_name');
    const bizNatureInput = document.getElementById('business_nature');

    // Income & Occupation Toggle elements
    const incomeToggle = document.getElementById('include_income_toggle');
    const incomeBox = document.getElementById('incomeFieldsBox');
    const occInput = document.getElementById('occupation_input');
    const incInput = document.getElementById('monthly_income_input');

    function toggleIncomeBox() {
        if (incomeToggle && incomeBox) {
            if (incomeToggle.checked) {
                incomeBox.style.display = 'block';
                if (occInput) occInput.required = true;
                if (incInput) incInput.required = true;
            } else {
                incomeBox.style.display = 'none';
                if (occInput) occInput.required = false;
                if (incInput) incInput.required = false;
            }
        }
    }

    if (incomeToggle) {
        incomeToggle.addEventListener('change', toggleIncomeBox);
        toggleIncomeBox();
    }

    const reqUl = document.getElementById('requirementsUl');
    const reqEmptyNotice = document.getElementById('requirementsEmptyNotice');
    const officialFeeBadge = document.getElementById('officialFeeBadge');

    const checkoutEmpty = document.getElementById('checkoutDocEmpty');
    const checkoutTableWrap = document.getElementById('checkoutDocTableWrap');
    const checkoutDocName = document.getElementById('checkoutDocName');
    const checkoutDocFee = document.getElementById('checkoutDocFee');
    const checkoutDocSubtotal = document.getElementById('checkoutDocSubtotal');
    const checkoutCashlessRow = document.getElementById('checkoutCashlessRow');
    const checkoutDocTotal = document.getElementById('checkoutDocTotal');

    const paymentRadios = document.querySelectorAll('.payment-radio');
    const cashlessFee = 10.00;

    const allOptions = [];
    docSelect.querySelectorAll('option').forEach(function (opt) {
        if (opt.value) {
            allOptions.push({
                id: opt.value,
                name: opt.dataset.name || opt.textContent.split('—')[0].trim(),
                fee: parseFloat(opt.dataset.fee) || 0,
                fullText: opt.textContent.toLowerCase()
            });
        }
    });

    if (searchInput && liveResults) {
        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();

            if (!query) {
                liveResults.style.display = 'none';
                liveResults.innerHTML = '';
                docSelect.querySelectorAll('option').forEach(opt => { opt.hidden = false; opt.disabled = false; });
                return;
            }

            const matches = allOptions.filter(item => item.fullText.includes(query) || item.name.toLowerCase().includes(query));

            docSelect.querySelectorAll('option').forEach(function (opt) {
                if (!opt.value) return;
                const matched = opt.textContent.toLowerCase().includes(query);
                opt.hidden = !matched;
                opt.disabled = !matched;
            });

            if (matches.length > 0) {
                let html = '';
                matches.forEach(function (m) {
                    html += '<button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-3 search-pick-btn" data-id="' + m.id + '">' +
                        '<div><span class="fw-bold text-dark">' + m.name + '</span></div>' +
                        '<span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1">₱' + m.fee.toFixed(2) + ' &bull; Select &rarr;</span>' +
                    '</button>';
                });
                liveResults.innerHTML = html;
                liveResults.style.display = 'block';

                liveResults.querySelectorAll('.search-pick-btn').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        const targetId = this.dataset.id;
                        docSelect.value = targetId;
                        docSelect.dispatchEvent(new Event('change'));
                        liveResults.style.display = 'none';
                        searchInput.value = '';
                        docSelect.querySelectorAll('option').forEach(opt => { opt.hidden = false; opt.disabled = false; });
                        updateView();
                    });
                });
            } else {
                liveResults.innerHTML = '<div class="list-group-item text-muted small py-2 text-center">No document found matching "' + query + '".</div>';
                liveResults.style.display = 'block';
            }
        });
    }

    function isCashlessSelected() {
        let isOnline = false;
        paymentRadios.forEach(function (radio) {
            if (radio.checked && radio.value !== 'cash') {
                isOnline = true;
            }
        });
        return isOnline;
    }

    function updateView() {
        const selected = docSelect.selectedOptions[0];

        if (!selected || !selected.value) {
            businessBox.style.display = 'none';
            if (bizNameInput) bizNameInput.required = false;
            if (bizNatureInput) bizNatureInput.required = false;

            reqEmptyNotice.style.display = 'block';
            reqUl.style.display = 'none';
            officialFeeBadge.textContent = 'Official Rate: ₱0.00';

            checkoutEmpty.style.display = 'block';
            checkoutTableWrap.style.display = 'none';
            return;
        }

        const name = selected.dataset.name || selected.textContent.split('—')[0].trim();
        const fee = parseFloat(selected.dataset.fee) || 0;
        const reqData = selected.dataset.requirements || '';

        // 1. Toggle Business Details
        const isBiz = name.toLowerCase().includes('business');
        if (isBiz) {
            businessBox.style.display = 'block';
            if (bizNameInput) bizNameInput.required = true;
            if (bizNatureInput) bizNatureInput.required = true;
        } else {
            businessBox.style.display = 'none';
            if (bizNameInput) bizNameInput.required = false;
            if (bizNatureInput) bizNatureInput.required = false;
        }

        // Auto-check income toggle if Loan is mentioned
        if (name.toLowerCase().includes('loan') || name.toLowerCase().includes('motor')) {
            if (incomeToggle && !incomeToggle.checked) {
                incomeToggle.checked = true;
                toggleIncomeBox();
            }
        }

        // 2. Parse Requirements
        officialFeeBadge.textContent = 'Official Rate: ₱' + fee.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        let reqItems = [];

        if (reqData) {
            if (reqData.startsWith('[') || reqData.startsWith('{')) {
                try {
                    const parsed = JSON.parse(reqData);
                    if (Array.isArray(parsed)) {
                        reqItems = parsed.map(p => typeof p === 'object' ? (p.item || p.name || '') : p);
                    }
                } catch (e) {
                    reqItems = reqData.split('||');
                }
            } else {
                reqItems = reqData.split('||');
            }
        }

        reqItems = reqItems.map(s => String(s).trim()).filter(Boolean);

        if (reqItems.length === 0) {
            reqItems = [
                'Valid Government-issued ID showing San Jose residence',
                'Community Tax Certificate (Cedula)',
                'Proof of Barangay Residency or Purok Certification'
            ];
        }

        let reqHtml = '';
        reqItems.forEach(function (item) {
            const formattedItem = item.charAt(0).toUpperCase() + item.slice(1);
            reqHtml += '<li class="d-flex align-items-center gap-2 text-dark small py-1">' +
                '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#198754" class="flex-shrink-0" viewBox="0 0 16 16">' +
                    '<path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>' +
                '</svg>' +
                '<span class="fw-medium">' + formattedItem + '</span>' +
            '</li>';
        });
        reqUl.innerHTML = reqHtml;
        reqEmptyNotice.style.display = 'none';
        reqUl.style.display = 'flex';

        // 3. Update Checkout Summary
        checkoutEmpty.style.display = 'none';
        checkoutTableWrap.style.display = 'block';

        checkoutDocName.textContent = name;
        const feeFormatted = '₱' + fee.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        checkoutDocFee.textContent = feeFormatted;
        checkoutDocSubtotal.textContent = feeFormatted;

        const isOnline = isCashlessSelected();
        if (isOnline) {
            checkoutCashlessRow.style.display = 'table-row';
        } else {
            checkoutCashlessRow.style.display = 'none';
        }

        const finalTotal = fee + (isOnline ? cashlessFee : 0);
        checkoutDocTotal.textContent = '₱' + finalTotal.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    docSelect.addEventListener('change', updateView);
    paymentRadios.forEach(r => r.addEventListener('change', updateView));

    updateView();
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