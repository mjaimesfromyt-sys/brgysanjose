@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">

            <div class="card shadow-sm">
                <div class="card-body p-4">

                    <h2 class="text-center mb-3">
                        Verify Your Email
                    </h2>

                    <p class="text-muted text-center mb-4">
                        We sent a 6-digit verification code to
                        <strong>{{ $user->email }}</strong>.
                        The code expires in 10 minutes.
                    </p>

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('otp.verify') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="otp" class="form-label">
                                Verification Code
                            </label>

                            <input
                                type="text"
                                name="otp"
                                id="otp"
                                class="form-control form-control-lg text-center @error('otp') is-invalid @enderror"
                                maxlength="6"
                                inputmode="numeric"
                                autocomplete="one-time-code"
                                placeholder="000000"
                                required
                                autofocus
                            >

                            @error('otp')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Verify Email
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-muted mb-2">
                            Didn't receive the code?
                        </p>

                        <form method="POST" action="{{ route('otp.resend') }}">
                            @csrf

                            <button type="submit" class="btn btn-link">
                                Resend verification code
                            </button>
                        </form>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection