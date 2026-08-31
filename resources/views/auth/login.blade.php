@extends('layouts.auth')
@section('title', 'Log in')

@section('content')
<h1 class="h4 fw-bold mb-1">Log in</h1>
<p class="text-muted mb-4">Access your barangay services account.</p>

<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <input id="email" type="email" name="email"
               value="{{ old('email') }}"
               class="form-control @error('email') is-invalid @enderror"
               autocomplete="email" required autofocus>
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input id="password" type="password" name="password"
               class="form-control @error('password') is-invalid @enderror"
               autocomplete="current-password" required>
        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-4 form-check">
        <input type="checkbox" name="remember" id="remember" class="form-check-input">
        <label for="remember" class="form-check-label">Keep me logged in</label>
    </div>

    <button type="submit" class="btn btn-primary w-100 btn-lg">Log in</button>
</form>

<hr class="my-4">

<p class="text-center mb-0">
    No account yet?
    <a href="{{ route('register') }}" class="fw-semibold text-decoration-none">Create one</a>
</p>
@endsection
