@extends('layouts.app')
@section('title', 'My Profile')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">

        <div class="mb-4">
            <a href="{{ route('dashboard') }}" class="text-decoration-none small fw-semibold">&larr; Dashboard</a>
            <h1 class="page-title mt-2">My Profile</h1>
            <p class="page-subtitle">
                Keep your details up to date so the barangay can reach you about
                your bookings, requests and rentals.
            </p>
        </div>

        {{-- Profile picture ------------------------------------------------ --}}
        <div class="card-soft p-4 mb-4">
            <h2 class="h6 fw-bold text-muted mb-3" style="text-transform:uppercase;letter-spacing:.07em">
                Profile picture
            </h2>

            <div class="d-flex flex-column flex-sm-row align-items-sm-start gap-4">
                @include('partials.avatar', ['user' => $me, 'size' => 112])

                <div class="flex-grow-1 w-100">
                    <form method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data">
                        @csrf

                        <label for="photo" class="form-label">Choose a photo</label>
                        <input id="photo" type="file" name="photo"
                               accept="image/jpeg,image/png,image/webp"
                               class="form-control @error('photo') is-invalid @enderror">
                        @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">
                            JPG, PNG or WebP, up to 5&nbsp;MB. It is cropped to a square,
                            so a head-and-shoulders photo works best.
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">
                            @include('partials.icon', ['name' => 'user', 'size' => 18])
                            <span class="ms-1">{{ $me->hasAvatar() ? 'Replace picture' : 'Upload picture' }}</span>
                        </button>
                    </form>

                    @if ($me->hasAvatar())
                        <form method="POST" action="{{ route('profile.photo.destroy') }}" class="mt-2"
                              onsubmit="return confirm('Remove your profile picture?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                @include('partials.icon', ['name' => 'trash', 'size' => 16])
                                <span class="ms-1">Remove picture</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Personal details ----------------------------------------------- --}}
        <div class="card-soft p-4 mb-4">
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')

                <h2 class="h6 fw-bold text-muted mb-3" style="text-transform:uppercase;letter-spacing:.07em">
                    Your name
                </h2>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label">First name</label>
                        <input id="first_name" type="text" name="first_name"
                               value="{{ old('first_name', $me->first_name) }}"
                               class="form-control @error('first_name') is-invalid @enderror" required>
                        @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="middle_name" class="form-label">
                            Middle name <span class="text-muted fw-normal">(optional)</span>
                        </label>
                        <input id="middle_name" type="text" name="middle_name"
                               value="{{ old('middle_name', $me->middle_name) }}"
                               class="form-control @error('middle_name') is-invalid @enderror">
                        @error('middle_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="last_name" class="form-label">Last name</label>
                        <input id="last_name" type="text" name="last_name"
                               value="{{ old('last_name', $me->last_name) }}"
                               class="form-control @error('last_name') is-invalid @enderror" required>
                        @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="suffix" class="form-label">
                            Suffix <span class="text-muted fw-normal">(optional)</span>
                        </label>
                        <input id="suffix" type="text" name="suffix" placeholder="Jr, Sr, III"
                               value="{{ old('suffix', $me->suffix) }}"
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
                        <input id="email" type="email" class="form-control" value="{{ $me->email }}" disabled>
                        <div class="form-text">
                            This is your log-in and cannot be changed here. Ask the
                            barangay office if you need it updated.
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="contact_no" class="form-label">Contact number</label>
                        <input id="contact_no" type="text" name="contact_no" placeholder="09XX XXX XXXX"
                               value="{{ old('contact_no', $me->contact_no) }}"
                               class="form-control @error('contact_no') is-invalid @enderror">
                        @error('contact_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Home address</label>
                    <textarea id="address" name="address" rows="2"
                              class="form-control @error('address') is-invalid @enderror">{{ old('address', $me->address) }}</textarea>
                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="purok" class="form-label">
                        Purok / Zone <span class="text-muted fw-normal">(optional)</span>
                    </label>
                    <input id="purok" type="text" name="purok" placeholder="e.g. Purok 1, Zone 2"
                           value="{{ old('purok', $me->purok) }}"
                           class="form-control @error('purok') is-invalid @enderror">
                    @error('purok') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                @if ($me->isActive())
                    <div class="alert alert-info py-2 small mb-3" role="status">
                        Your account is already verified by the barangay. If you change
                        your name or address, staff may ask you to confirm it in person.
                    </div>
                @endif

                <button type="submit" class="btn btn-primary">Save changes</button>
            </form>
        </div>

        {{-- Password -------------------------------------------------------- --}}
        <div class="card-soft p-4">
            <form method="POST" action="{{ route('profile.password') }}">
                @csrf
                @method('PUT')

                <h2 class="h6 fw-bold text-muted mb-3" style="text-transform:uppercase;letter-spacing:.07em">
                    Change password
                </h2>

                <div class="mb-3">
                    <label for="current_password" class="form-label">Current password</label>
                    <input id="current_password" type="password" name="current_password"
                           class="form-control @error('current_password') is-invalid @enderror"
                           autocomplete="current-password">
                    @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">New password</label>
                        <input id="password" type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               autocomplete="new-password">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">Confirm new password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation"
                               class="form-control" autocomplete="new-password">
                    </div>
                </div>

                <div class="form-text mb-3">
                    At least 8 characters. Changing it signs you out everywhere else.
                </div>

                <button type="submit" class="btn btn-primary">Change password</button>
            </form>
        </div>

    </div>
</div>
@endsection
