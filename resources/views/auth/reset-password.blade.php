@extends('layouts.auth')
@section('title', 'Reset password')

@section('content')
<h1 class="h4 fw-bold mb-1">Choose a new password</h1>
<p class="text-muted mb-4">
    You're setting a new password for <strong>{{ $email }}</strong>.
</p>

<form method="POST" action="{{ route('password.update') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">
    <input type="hidden" name="email" value="{{ $email }}">

    <div class="mb-3">
        <label for="password" class="form-label">New password</label>
        <input id="password"
               type="password"
               name="password"
               class="form-control @error('password') is-invalid @enderror"
               autocomplete="new-password"
               required
               autofocus>

        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label for="password_confirmation" class="form-label">Confirm new password</label>
        <input id="password_confirmation"
               type="password"
               name="password_confirmation"
               class="form-control @error('password') is-invalid @enderror"
               autocomplete="new-password"
               required>

        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary w-100 btn-lg">
        Reset password
    </button>
</form>

<hr class="my-4">

<p class="text-center mb-0">
    <a href="{{ route('login') }}" class="fw-semibold text-decoration-none">
        Back to log in
    </a>
</p>
@endsection
