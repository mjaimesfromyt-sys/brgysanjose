@extends('layouts.auth')
@section('title', 'Forgot password')

@section('content')
<h1 class="h4 fw-bold mb-1">Forgot your password?</h1>
<p class="text-muted mb-4">
    Enter the email address you registered with and we'll send you a reset link.
</p>

<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <input id="email"
               type="email"
               name="email"
               value="{{ old('email') }}"
               class="form-control @error('email') is-invalid @enderror"
               autocomplete="email"
               required
               autofocus>

        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary w-100 btn-lg">
        Send reset link
    </button>
</form>

<hr class="my-4">

<p class="text-center mb-0">
    Remembered it after all?
    <a href="{{ route('login') }}" class="fw-semibold text-decoration-none">
        Back to log in
    </a>
</p>
@endsection
