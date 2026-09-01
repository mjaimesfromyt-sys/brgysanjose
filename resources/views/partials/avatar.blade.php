{{--
    A resident's profile picture, falling back to their initials.

    Photos are streamed by ProfileController::photo() because the live host
    denies /storage; the ?v token is derived from the stored filename so a
    replaced photo never shows from cache.

    Sizing is inline rather than in app.scss so this needs no Vite rebuild to
    reach the server (see docs/DEPLOY-HOSTINGER.md).

    Usage: @include('partials.avatar')                       (signed-in user, 32px)
           @include('partials.avatar', ['user' => $resident, 'size' => 96])
--}}
@php
    $avatarUser = $user ?? auth()->user();
    $avatarSize = $size ?? 32;
@endphp

@if ($avatarUser?->hasAvatar())
    <img src="{{ route('profile.photo', ['user' => $avatarUser, 'v' => substr(sha1($avatarUser->avatar_path), 0, 8)]) }}"
         alt="Profile picture of {{ $avatarUser->name }}"
         width="{{ $avatarSize }}" height="{{ $avatarSize }}"
         class="rounded-circle object-fit-cover flex-shrink-0"
         style="width:{{ $avatarSize }}px;height:{{ $avatarSize }}px;border:1px solid rgba(0,0,0,.08)">
@else
    <span class="rounded-circle flex-shrink-0 d-inline-flex align-items-center justify-content-center fw-bold"
          aria-hidden="true"
          style="width:{{ $avatarSize }}px;height:{{ $avatarSize }}px;background:#e6efe9;color:#0b5e35;
                 font-size:{{ max(11, (int) round($avatarSize * 0.4)) }}px;line-height:1">{{ $avatarUser?->initials }}</span>
@endif
