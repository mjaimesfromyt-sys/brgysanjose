@extends('layouts.auth')
@section('title', 'Register')
@section('auth-width', '44rem')

@section('content')
<h1 class="h4 fw-bold mb-1">Create your account</h1>
<p class="text-muted mb-4">
    A barangay staff member will review and verify your account before booking
    and document requests are enabled.
</p>

<form method="POST" action="{{ route('register') }}">
    @csrf

    <h2 class="h6 fw-bold text-muted mb-3" style="text-transform:uppercase;letter-spacing:.07em">
        Your name
    </h2>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="first_name" class="form-label">First name</label>
            <input id="first_name" type="text" name="first_name"
                   value="{{ old('first_name') }}"
                   class="form-control @error('first_name') is-invalid @enderror" required autofocus>
            @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6 mb-3">
            <label for="middle_name" class="form-label">
                Middle name <span class="text-muted fw-normal">(optional)</span>
            </label>
            <input id="middle_name" type="text" name="middle_name"
                   value="{{ old('middle_name') }}"
                   class="form-control @error('middle_name') is-invalid @enderror">
            @error('middle_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mb-3">
            <label for="last_name" class="form-label">Last name</label>
            <input id="last_name" type="text" name="last_name"
                   value="{{ old('last_name') }}"
                   class="form-control @error('last_name') is-invalid @enderror" required>
            @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4 mb-3">
            <label for="suffix" class="form-label">
                Suffix <span class="text-muted fw-normal">(optional)</span>
            </label>
            <input id="suffix" type="text" name="suffix" placeholder="Jr, Sr, III"
                   value="{{ old('suffix') }}"
                   class="form-control @error('suffix') is-invalid @enderror">
            @error('suffix') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <hr class="my-4">

    <h2 class="h6 fw-bold text-muted mb-3" style="text-transform:uppercase;letter-spacing:.07em">
        Contact &amp; address
    </h2>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="email" class="form-label">Email address</label>
            <input id="email" type="email" name="email"
                   value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   autocomplete="email" required>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="contact_no" class="form-label">Contact number</label>
            <input id="contact_no" type="text" name="contact_no" placeholder="09XX XXX XXXX"
                   value="{{ old('contact_no') }}"
                   class="form-control @error('contact_no') is-invalid @enderror">
            @error('contact_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="mb-3">
        <label for="address" class="form-label">Home address</label>
        <textarea id="address" name="address" rows="2"
                  class="form-control @error('address') is-invalid @enderror">{{ old('address') }}</textarea>
        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label for="purok" class="form-label">
            Purok / Zone <span class="text-muted fw-normal">(optional)</span>
        </label>
        <input id="purok" type="text" name="purok" placeholder="e.g. Purok 1, Zone 2"
               value="{{ old('purok') }}"
               class="form-control @error('purok') is-invalid @enderror">
        @error('purok') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label d-block">Are you a barangay resident?</label>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="declared_type"
                   id="dt_resident" value="resident"
                   {{ old('declared_type', 'resident') === 'resident' ? 'checked' : '' }}>
            <label class="form-check-label" for="dt_resident">Yes, I live here</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="declared_type"
                   id="dt_non" value="non_resident"
                   {{ old('declared_type') === 'non_resident' ? 'checked' : '' }}>
            <label class="form-check-label" for="dt_non">No, I have business here</label>
        </div>
        @error('declared_type') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        <div class="form-text">The barangay will confirm this when reviewing your account.</div>
    </div>

    <hr class="my-4">

    <h2 class="h6 fw-bold text-muted mb-3" style="text-transform:uppercase;letter-spacing:.07em">
        Password
    </h2>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="password" class="form-label">Password</label>
            <input id="password" type="password" name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   autocomplete="new-password" required>
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="password_confirmation" class="form-label">Confirm password</label>
            <input id="password_confirmation" type="password"
                   name="password_confirmation" class="form-control"
                   autocomplete="new-password" required>
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 btn-lg mt-2">Register</button>
</form>

<hr class="my-4">

<p class="text-center mb-0">
    Already have an account?
    <a href="{{ route('login') }}" class="fw-semibold text-decoration-none">Log in</a>
</p>
@endsection
