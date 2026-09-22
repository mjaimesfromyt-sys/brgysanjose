@extends('layouts.auth')
@section('title', 'Register — Barangay San Jose Resident Verification')
@section('auth-width', '52rem')

@section('content')
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

<style>
.google-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    width: 100%;
    padding: 12px 16px;
    background-color: #ffffff;
    border: 1.5px solid #dadce0;
    border-radius: 50px;
    color: #3c4043;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    box-shadow: 0 1px 3px rgba(60,64,67, 0.08);
    transition: all 0.2s ease;
}
.google-btn:hover {
    background-color: #f8fafc;
    border-color: #c2c7d0;
    color: #202124;
    box-shadow: 0 2px 6px rgba(60,64,67, 0.15);
}
</style>

<div class="mb-4 text-center">
    <img src="{{ asset('images/barangay-seal.png') }}" alt="Barangay San Jose Seal" style="width: 64px; height: 64px; object-fit: contain;" class="mb-2">
    <h1 class="h4 fw-bold mb-1 text-dark">Resident Registration & Verification</h1>
    <p class="text-muted small mb-0">
        Barangay San Jose, Talibon, Bohol. Please provide accurate details for official barangay certification and identity validation.
    </p>
</div>

{{-- 👉 1. CONTINUE WITH GOOGLE BUTTON --}}


{{-- DIVIDER --}}
<div class="position-relative text-center mb-4">
    <hr class="text-secondary opacity-25">
    <span class="position-absolute top-50 start-50 translate-middle px-3 bg-white text-muted small fw-semibold">
        or fill in your details manually below
    </span>
</div>

@if ($errors->any())
    <div class="alert alert-danger rounded-3 shadow-sm mb-4">
        <div class="fw-bold mb-1">Please fix the errors below:</div>
        <ul class="mb-0 ps-3 small">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" id="registerForm">
    @csrf

    {{-- SECTION 1: PERSONAL IDENTITY --}}
    <div class="card border border-light-subtle shadow-sm rounded-4 mb-4">
        <div class="card-header bg-light py-3 px-4 border-0">
            <h6 class="fw-bold mb-0 text-dark">1. Personal Identity</h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-secondary">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-secondary">Middle Name</label>
                    <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-secondary">Suffix</label>
                    <input type="text" name="suffix" class="form-control" placeholder="Jr., III" value="{{ old('suffix') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Date of Birth <span class="text-danger">*</span></label>
                    <input type="date" name="birthdate" class="form-control" value="{{ old('birthdate') }}" max="{{ now()->subYears(12)->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Sex / Gender <span class="text-danger">*</span></label>
                    <select name="gender" class="form-select" required>
                        <option value="" disabled selected>-- Select Sex --</option>
                        <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Civil Status <span class="text-danger">*</span></label>
                    <select name="civil_status" class="form-select" required>
                        <option value="" disabled selected>-- Select Status --</option>
                        <option value="Single" {{ old('civil_status') == 'Single' ? 'selected' : '' }}>Single</option>
                        <option value="Married" {{ old('civil_status') == 'Married' ? 'selected' : '' }}>Married</option>
                        <option value="Widowed" {{ old('civil_status') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                        <option value="Separated" {{ old('civil_status') == 'Separated' ? 'selected' : '' }}>Separated</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Religion <span class="text-danger">*</span></label>
                    <input type="text" name="religion" class="form-control" placeholder="e.g. Roman Catholic, Iglesia ni Cristo, UCCP" value="{{ old('religion', 'Roman Catholic') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Citizenship <span class="text-danger">*</span></label>
                    <input type="text" name="citizenship" class="form-control" value="{{ old('citizenship', 'Filipino') }}" required>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION 2: BARANGAY RESIDENCY & CONTACT --}}
    <div class="card border border-light-subtle shadow-sm rounded-4 mb-4">
        <div class="card-header bg-light py-3 px-4 border-0">
            <h6 class="fw-bold mb-0 text-dark">2. Barangay Residency & Contact Details</h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Purok <span class="text-danger">*</span></label>
                    <select name="purok" class="form-select" required>
                        <option value="" disabled selected>-- Select Your Purok --</option>
                        @for($i = 1; $i <= 7; $i++)
                            <option value="Purok {{ $i }}" {{ old('purok') == "Purok $i" ? 'selected' : '' }}>Purok {{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Contact Number (Mobile) <span class="text-danger">*</span></label>
                    <input type="text" name="contact_no" class="form-control" placeholder="09XXXXXXXXX" value="{{ old('contact_no') }}" required>
                </div>

                <div class="col-12">
                    <label class="form-label small fw-semibold text-secondary">Complete Home Address in Barangay San Jose <span class="text-danger">*</span></label>
                    <input type="text" name="address" class="form-control" placeholder="House No. / Street / Sitio, Barangay San Jose, Talibon, Bohol" value="{{ old('address') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Length of Stay / Residency</label>
                    <input type="text" name="length_of_stay" class="form-control" placeholder="e.g., Since Birth, 10 years, 5 years" value="{{ old('length_of_stay') }}">
                </div>
                <div class="col-md-6 d-flex align-items-center pt-md-3">
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="is_voter" id="is_voter" value="1" {{ old('is_voter') ? 'checked' : '' }}>
                        <label class="form-check-label small fw-semibold text-dark cursor-pointer" for="is_voter">
                            Registered Voter in Barangay San Jose, Talibon
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION 3: VALID ID VERIFICATION (CAMERA & GALLERY OPTIONS) --}}
    <div class="card border border-light-subtle shadow-sm rounded-4 mb-4">
        <div class="card-header bg-light py-3 px-4 border-0">
            <h6 class="fw-bold mb-0 text-dark">3. Identity Verification (Valid Government ID)</h6>
        </div>
        <div class="card-body p-4">
            <p class="text-muted small mb-3">
                To guarantee that only bona fide residents are verified, please take a clear photo or upload your government-issued ID showing your identity and residency.
            </p>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Valid ID Type <span class="text-danger">*</span></label>
                    <select name="id_type" class="form-select" required>
                        <option value="" disabled selected>-- Select Government ID --</option>
                        <option value="PhilSys National ID" {{ old('id_type') == 'PhilSys National ID' ? 'selected' : '' }}>PhilSys National ID</option>
                        <option value="Voter's ID / Certification" {{ old('id_type') == "Voter's ID / Certification" ? 'selected' : '' }}>Voter's ID / Comelec Certification</option>
                        <option value="Driver's License" {{ old('id_type') == "Driver's License" ? 'selected' : '' }}>Driver's License</option>
                        <option value="UMID / SSS ID" {{ old('id_type') == 'UMID / SSS ID' ? 'selected' : '' }}>UMID / SSS ID</option>
                        <option value="Postal ID" {{ old('id_type') == 'Postal ID' ? 'selected' : '' }}>Postal ID</option>
                        <option value="Senior Citizen / OSCA ID" {{ old('id_type') == 'Senior Citizen / OSCA ID' ? 'selected' : '' }}>Senior Citizen / OSCA ID</option>
                        <option value="PWD ID" {{ old('id_type') == 'PWD ID' ? 'selected' : '' }}>PWD ID</option>
                        <option value="Student ID (School ID)" {{ old('id_type') == 'Student ID (School ID)' ? 'selected' : '' }}>Student ID (for minors/students)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">ID Card Number <span class="text-danger">*</span></label>
                    <input type="text" name="id_number" class="form-control" placeholder="e.g., 1234-5678-9012" value="{{ old('id_number') }}" required>
                </div>

                {{-- DUAL CAMERA & GALLERY BUTTONS WITH LIVE PREVIEW --}}
                <div class="col-12">
                    <label class="form-label small fw-semibold text-secondary d-block mb-2">Upload ID Photo (Front) <span class="text-danger">*</span></label>
                    
                    <input type="file" name="id_photo" id="primaryIdFileInput" accept="image/*" style="display: none;" required>
                    <input type="file" id="cameraDirectInput" accept="image/*" capture="environment" style="display: none;">

                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <button type="button" class="btn btn-outline-success rounded-pill px-3 py-2 d-flex align-items-center gap-2 shadow-sm" onclick="document.getElementById('cameraDirectInput').click()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M15 12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h1.172a3 3 0 0 0 2.12-.879l.83-.828A1 1 0 0 1 7.829 3h.342a1 1 0 0 1 .707.293l.828.828A3 3 0 0 0 11.828 5H13a1 1 0 0 1 1 1zM2 4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-1.172a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 9.172 2H6.828a2 2 0 0 0-1.414.586l-.828.828A2 2 0 0 1 3.172 4z"/>
                                <path d="M8 11a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5m0 1a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7M3 6.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0"/>
                            </svg>
                            <span class="fw-semibold">Take Photo with Camera</span>
                        </button>

                        <button type="button" class="btn btn-outline-secondary rounded-pill px-3 py-2 d-flex align-items-center gap-2 shadow-sm" onclick="document.getElementById('primaryIdFileInput').click()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                                <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/>
                            </svg>
                            <span class="fw-semibold">Choose from Gallery / Files</span>
                        </button>
                    </div>

                    <div id="idPhotoPreviewContainer" class="p-3 bg-light rounded-3 border mt-2 shadow-sm" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-success small fw-bold d-flex align-items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                </svg>
                                Valid ID Photo Captured / Selected
                            </span>
                            <button type="button" class="btn btn-sm btn-link text-danger text-decoration-none p-0 fw-semibold" onclick="clearIdPhoto()">
                                Remove & Retake
                            </button>
                        </div>
                        <div class="text-center p-2 bg-white rounded border">
                            <img id="idPhotoPreviewImg" src="" alt="Valid ID Preview" class="img-fluid rounded" style="max-height: 240px; object-fit: contain;">
                        </div>
                        <div class="text-muted small text-center mt-2" id="idPhotoMetaText"></div>
                    </div>

                    <div class="form-text text-muted small mt-1">Max 5MB. Accepted formats: JPG, PNG, WEBP. Make sure all text on the ID is sharp and readable.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION 4: ACCOUNT CREDENTIALS --}}
    <div class="card border border-light-subtle shadow-sm rounded-4 mb-4">
        <div class="card-header bg-light py-3 px-4 border-0">
            <h6 class="fw-bold mb-0 text-dark">4. Account Login Credentials</h6>
        </div>
        <div class="card-body p-4">
            <input type="hidden" name="declared_type" value="resident">

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label small fw-semibold text-secondary">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" placeholder="you@example.com" value="{{ old('email') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Password <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <input type="password" name="password" id="regPassword" class="form-control" placeholder="Minimum 8 characters" required>
                        <button type="button" class="btn btn-outline-secondary" id="toggleRegPassword" aria-label="Show password" title="Show / hide password">👁</button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Confirm Password <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <input type="password" name="password_confirmation" id="regPasswordConfirm" class="form-control" required>
                        <button type="button" class="btn btn-outline-secondary" id="toggleRegPasswordConfirm" aria-label="Show password" title="Show / hide password">👁</button>
                    </div>
                </div>
            </div>

<script>
(function () {
    function bindToggle(btnId, inputId) {
        const btn = document.getElementById(btnId);
        const input = document.getElementById(inputId);
        if (!btn || !input) return;
        btn.addEventListener('click', function () {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.textContent = show ? '🙈' : '👁';
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    }
    bindToggle('toggleRegPassword', 'regPassword');
    bindToggle('toggleRegPasswordConfirm', 'regPasswordConfirm');
})();
</script>

            @if(config('services.turnstile.site_key'))
                <div class="mt-4 d-flex justify-content-center">
                    <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"></div>
                </div>
                @error('cf-turnstile-response')
                    <div class="text-danger small text-center mt-1">{{ $message }}</div>
                @enderror
            @endif
        </div>
    </div>

    {{-- Submit Button --}}
    <div class="d-grid gap-2 mb-4">
        <button type="submit" class="btn btn-success btn-lg rounded-pill fw-bold py-3 shadow-sm" id="submitBtn">
            Complete Registration & Submit for Verification &rarr;
        </button>
    </div>

    <div class="text-center small text-muted">
        Already have a verified account? <a href="{{ route('login') }}" class="text-success fw-bold text-decoration-none">Log in here</a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const primaryInput = document.getElementById('primaryIdFileInput');
    const cameraInput = document.getElementById('cameraDirectInput');
    const previewContainer = document.getElementById('idPhotoPreviewContainer');
    const previewImg = document.getElementById('idPhotoPreviewImg');
    const metaText = document.getElementById('idPhotoMetaText');

    function handleFileSelection(file) {
        if (!file) return;

        if (file.size > 5 * 1024 * 1024) {
            alert('File size exceeds 5MB. Please capture or select a smaller image.');
            clearIdPhoto();
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            previewImg.src = e.target.result;
            metaText.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
            previewContainer.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }

    // When chosen from Gallery / Files
    primaryInput.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            handleFileSelection(this.files[0]);
        }
    });

    // When taken with Camera
    cameraInput.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(this.files[0]);
            primaryInput.files = dataTransfer.files;

            handleFileSelection(this.files[0]);
        }
    });

    window.clearIdPhoto = function () {
        primaryInput.value = '';
        cameraInput.value = '';
        previewImg.src = '';
        previewContainer.style.display = 'none';
        metaText.textContent = '';
    };
});
</script>
@endsection
