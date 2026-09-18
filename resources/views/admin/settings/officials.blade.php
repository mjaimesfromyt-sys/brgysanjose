@extends('layouts.admin')
@section('title', 'Barangay Officials Settings')

@section('content')
<div class="container-fluid py-3" style="max-width: 800px;">

    <div class="mb-4">
        <h1 class="h3 fw-bold mb-1 text-dark">Barangay Officials &amp; Letterhead Settings</h1>
        <p class="text-muted small mb-0">Update the official signatories and contact information that appear on all generated certificates.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="background-color: #e8f5e9; color: #166534;">
            <strong>✓ Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="border: 1px solid #e2e8f0;">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <span>🏛️</span> Official Signatories &amp; Letterhead (Super Admin Only)
            </h6>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.settings.officials.update') }}">
                @csrf
                @method('PUT')

                <!-- Punong Barangay -->
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark small">Punong Barangay (Captain) Name</label>
                    <input type="text" name="captain_name" class="form-control" 
                           value="{{ old('captain_name', $settings['captain_name'] ?? 'JOSEFINA C. GURREA') }}" required>
                    <div class="form-text small">This will appear under "Noted by" on all certificates.</div>
                </div>

                <!-- Barangay Secretary -->
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark small">Barangay Secretary Name</label>
                    <input type="text" name="secretary_name" class="form-control" 
                           value="{{ old('secretary_name', $settings['secretary_name'] ?? 'HANNAH JOY B. CREDO') }}" required>
                    <div class="form-text small">This will appear under "Records verified by" on all certificates.</div>
                </div>

                <hr class="my-4 text-muted">

                <!-- Address -->
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark small">Barangay Hall Address</label>
                    <input type="text" name="barangay_address" class="form-control" 
                           value="{{ old('barangay_address', $settings['barangay_address'] ?? 'PUROK 5, SAN JOSE, TALIBON, BOHOL') }}" required>
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark small">Official Email Address (Footer)</label>
                    <input type="email" name="barangay_email" class="form-control" 
                           value="{{ old('barangay_email', $settings['barangay_email'] ?? 'blgusanjosetalibon1910@gmail.com') }}" required>
                </div>

                <!-- Facebook -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-dark small">Official Facebook Page (Footer)</label>
                    <input type="text" name="barangay_facebook" class="form-control" 
                           value="{{ old('barangay_facebook', $settings['barangay_facebook'] ?? 'Barangay San Jose - Official') }}" required>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success px-4 py-2 fw-bold shadow-sm">
                        Save Official Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection