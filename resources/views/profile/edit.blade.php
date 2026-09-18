@extends('layouts.app')
@section('title', 'My Profile')

@section('content')
@php
    $isGoogleUser = !empty($user->google_id) || $user->registration_method === 'google';
@endphp

<div class="container py-4" style="max-width: 660px;">

    <!-- Success Messages -->
    @if (session('status_photo'))
        <div class="alert alert-success border-0 py-2.5 px-3 mb-3 rounded-3 shadow-sm" style="background-color: #e8f5e9; color: #1b5e20; font-size: 13.5px;">
            {{ session('status_photo') }}
        </div>
    @endif

    @if (session('status_details'))
        <div class="alert alert-success border-0 py-2.5 px-3 mb-3 rounded-3 shadow-sm" style="background-color: #e8f5e9; color: #1b5e20; font-size: 13.5px;">
            {{ session('status_details') }}
        </div>
    @endif

    @if (session('status_password'))
        <div class="alert alert-success border-0 py-2.5 px-3 mb-3 rounded-3 shadow-sm" style="background-color: #e8f5e9; color: #1b5e20; font-size: 13.5px;">
            {{ session('status_password') }}
        </div>
    @endif

    <!-- Back to Dashboard -->
    <div class="mb-2">
        <a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold small d-inline-flex align-items-center gap-1" style="color: #1b5e20;">
            <span>&larr;</span> Dashboard
        </a>
    </div>

    <!-- Header -->
    <div class="mb-4">
        <h2 class="h4 fw-bold mb-1" style="color: #0f172a;">My Profile</h2>
        <p class="text-muted small mb-0">Keep your details up to date so the barangay can reach you about your bookings, requests and rentals.</p>
    </div>

    <!-- ================= 1. PROFILE PICTURE CARD ================= -->
    <div class="card border mb-4 shadow-sm" style="border-radius: 12px; border-color: #e2e8f0;">
        <div class="card-body p-4">
            <h6 class="text-uppercase fw-bold mb-3" style="font-size: 12px; letter-spacing: 0.8px; color: #334155;">
                PROFILE PICTURE
            </h6>

            <div class="d-flex align-items-start gap-4">
                <div style="flex-shrink: 0;">
                    @if ($user->avatar_url)
                        <img id="avatarPreview"
                             src="{{ $user->avatar_url }}" 
                             alt="{{ $user->first_name }}" 
                             class="rounded-circle shadow-sm" 
                             style="width: 88px; height: 88px; object-fit: cover; border: 2px solid #e2e8f0;">
                    @else
                        <div id="avatarInitials" 
                             class="rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" 
                             style="width: 88px; height: 88px; background-color: #e8f5e9; color: #1b5e20; font-size: 30px; font-family: ui-sans-serif, system-ui, sans-serif;">
                            {{ $user->initials }}
                        </div>
                    @endif
                </div>

                <div class="flex-grow-1">
                    <form method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data">
                        @csrf
                        <label class="form-label fw-bold mb-1" style="font-size: 13px; color: #1e293b;">Choose a photo</label>
                        
                        <input type="file" 
                               id="profile_picture" 
                               name="profile_picture" 
                               class="form-control form-control-sm mb-1 @error('profile_picture') is-invalid @enderror" 
                               accept="image/png, image/jpeg, image/jpg, image/webp" 
                               onchange="previewPhoto(event)" 
                               required>
                        @error('profile_picture')
                            <div class="invalid-feedback mb-1">{{ $message }}</div>
                        @enderror

                        <p class="text-muted mb-3" style="font-size: 11.5px; line-height: 1.4;">
                            JPG, PNG or WebP, up to 5 MB. It is cropped to a square, so a head-and-shoulders photo works best.
                        </p>

                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            @if ($user->avatar_url)
                                <button type="submit" class="btn text-white px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5" style="background-color: #1b5e20; font-size: 13px; border-radius: 6px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/><path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/></svg>
                                    Replace picture
                                </button>
                            @else
                                <button type="submit" class="btn text-white px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5" style="background-color: #1b5e20; font-size: 13px; border-radius: 6px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/><path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/></svg>
                                    Upload picture
                                </button>
                            @endif
                        </div>
                    </form>

                    @if ($user->avatar_url)
                        <form method="POST" action="{{ route('profile.photo.remove') }}" class="mt-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-secondary px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1.5" style="font-size: 12px; border-radius: 6px;" onclick="return confirm('Are you sure you want to remove your profile picture?')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/><path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/></svg>
                                Remove picture
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ================= 2. PERSONAL DETAILS CARD ================= -->
    <div class="card border mb-4 shadow-sm" style="border-radius: 12px; border-color: #e2e8f0;">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('profile.details.update') }}">
                @csrf
                @method('PUT')

                <h6 class="text-uppercase fw-bold mb-3" style="font-size: 12px; letter-spacing: 0.8px; color: #334155;">
                    YOUR NAME
                </h6>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="first_name" class="form-label fw-bold mb-1" style="font-size: 13px; color: #1e293b;">First name</label>
                        <input type="text" id="first_name" name="first_name" class="form-control form-control-sm @error('first_name') is-invalid @enderror" value="{{ old('first_name', $user->first_name) }}" required>
                        @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="middle_name" class="form-label fw-bold mb-1" style="font-size: 13px; color: #1e293b;">Middle name <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="text" id="middle_name" name="middle_name" class="form-control form-control-sm @error('middle_name') is-invalid @enderror" value="{{ old('middle_name', $user->middle_name) }}">
                        @error('middle_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="last_name" class="form-label fw-bold mb-1" style="font-size: 13px; color: #1e293b;">Last name</label>
                        <input type="text" id="last_name" name="last_name" class="form-control form-control-sm @error('last_name') is-invalid @enderror" value="{{ old('last_name', $user->last_name) }}" required>
                        @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="suffix" class="form-label fw-bold mb-1" style="font-size: 13px; color: #1e293b;">Suffix <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="text" id="suffix" name="suffix" placeholder="Jr, Sr, III" class="form-control form-control-sm @error('suffix') is-invalid @enderror" value="{{ old('suffix', $user->suffix) }}">
                        @error('suffix') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <hr class="my-4" style="border-color: #f1f5f9;">

                <h6 class="text-uppercase fw-bold mb-3" style="font-size: 12px; letter-spacing: 0.8px; color: #334155;">
                    CONTACT & ADDRESS
                </h6>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="email" class="form-label fw-bold mb-1" style="font-size: 13px; color: #1e293b;">Email address</label>
                        <input type="email" id="email" class="form-control form-control-sm" style="background-color: #f1f5f9; color: #64748b;" value="{{ $user->email }}" readonly disabled>
                        <p class="text-muted mt-1 mb-0" style="font-size: 11px; line-height: 1.3;">
                            This is your log-in and cannot be changed here. Ask the barangay office if you need it updated.
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label for="contact_no" class="form-label fw-bold mb-1" style="font-size: 13px; color: #1e293b;">Contact number</label>
                        <input type="text" id="contact_no" name="contact_no" class="form-control form-control-sm @error('contact_no') is-invalid @enderror" placeholder="0917 555 1234" value="{{ old('contact_no', $user->contact_no) }}">
                        @error('contact_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label fw-bold mb-1" style="font-size: 13px; color: #1e293b;">Home address</label>
                    <textarea id="address" name="address" rows="2" class="form-control form-control-sm @error('address') is-invalid @enderror" placeholder="Purok 5, San Jose, Talibon, Bohol">{{ old('address', $user->address) }}</textarea>
                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="purok" class="form-label fw-bold mb-1" style="font-size: 13px; color: #1e293b;">Purok / Zone <span class="text-muted fw-normal">(optional)</span></label>
                    <input type="text" id="purok" name="purok" placeholder="Purok 5" class="form-control form-control-sm @error('purok') is-invalid @enderror" value="{{ old('purok', $user->purok) }}">
                    @error('purok') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="p-2.5 px-3 mb-4 rounded-3" style="background-color: #eff6ff; border: 1px solid #bfdbfe; font-size: 12px; color: #1e40af; line-height: 1.4;">
                    Your account is already verified by the barangay. If you change your name or address, staff may ask you to confirm it in person.
                </div>

                <button type="submit" class="btn text-white px-4 py-1.5 fw-semibold" style="background-color: #1b5e20; font-size: 13.5px; border-radius: 6px;">
                    Save changes
                </button>
            </form>
        </div>
    </div>

    <!-- ================= 3. CHANGE / SET PASSWORD CARD ================= -->
    <div class="card border shadow-sm" style="border-radius: 12px; border-color: #e2e8f0;">
        <div class="card-body p-4">
            <h6 class="text-uppercase fw-bold mb-3" style="font-size: 12px; letter-spacing: 0.8px; color: #334155;">
                {{ $isGoogleUser ? 'SET ACCOUNT PASSWORD' : 'CHANGE PASSWORD' }}
            </h6>

            @if ($isGoogleUser)
                <div class="p-2.5 px-3 mb-3 rounded-3 d-flex align-items-center gap-2" style="background-color: #f8fafc; border: 1px solid #e2e8f0; font-size: 12px; color: #475569;">
                    <span>You registered with <strong>Google</strong>. You don't need a current password. You can set a password below if you also want to log in using email & password.</span>
                </div>
            @endif

            <form method="POST" action="{{ route('profile.password.update') }}">
                @csrf
                @method('PUT')

                @if (!$isGoogleUser)
                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-bold mb-1" style="font-size: 13px; color: #1e293b;">Current password</label>
                        <input type="password" id="current_password" name="current_password" class="form-control form-control-sm @error('current_password') is-invalid @enderror" required>
                        @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                @endif

                <div class="row g-3 mb-2">
                    <div class="col-md-6">
                        <label for="password" class="form-label fw-bold mb-1" style="font-size: 13px; color: #1e293b;">New password</label>
                        <input type="password" id="password" name="password" class="form-control form-control-sm @error('password') is-invalid @enderror" placeholder="Enter new password" required>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label fw-bold mb-1" style="font-size: 13px; color: #1e293b;">Confirm new password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control form-control-sm" placeholder="Confirm new password" required>
                    </div>
                </div>

                <p class="text-muted mb-3" style="font-size: 11.5px;">
                    At least 8 characters. {{ $isGoogleUser ? 'Setting a password allows you to log in with either Google or email & password.' : 'Changing it signs you out everywhere else.' }}
                </p>

                <button type="submit" class="btn text-white px-4 py-1.5 fw-semibold" style="background-color: #1b5e20; font-size: 13.5px; border-radius: 6px;">
                    {{ $isGoogleUser ? 'Set password' : 'Change password' }}
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function previewPhoto(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            let img = document.getElementById('avatarPreview');
            const initials = document.getElementById('avatarInitials');
            
            if (!img && initials) {
                img = document.createElement('img');
                img.id = 'avatarPreview';
                img.className = 'rounded-circle shadow-sm';
                img.style = 'width: 88px; height: 88px; object-fit: cover; border: 2px solid #e2e8f0;';
                initials.parentNode.replaceChild(img, initials);
            }
            if (img) {
                img.src = e.target.result;
            }
        };
        reader.readAsDataURL(file);
    }
}
</script>
@endsection