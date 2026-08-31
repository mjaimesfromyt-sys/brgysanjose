{{--
    Official seal image, with a safe fallback.

    If the PNG has not been added to public/images yet, this renders the plain
    "SJ" monogram instead of a broken image — so the site never looks broken
    while the assets are still being added.

    Usage:
        @include('partials.seal', ['seal' => 'barangay', 'class' => 'crest__seal'])
        @include('partials.seal', ['seal' => 'talibon',  'class' => 'crest__seal', 'imageOnly' => true])
--}}
@php
    $sealName   = $seal ?? 'barangay';
    $sealFile   = "images/{$sealName}-seal.png";
    $sealExists = file_exists(public_path($sealFile));
    $sealAlt    = $sealName === 'talibon'
        ? 'Seal of the Municipality of Talibon, Bohol'
        : 'Seal of Barangay San Jose';
@endphp

@if ($sealExists)
    <img src="{{ asset($sealFile) }}" alt="{{ $sealAlt }}"
         class="{{ $class ?? 'seal' }}" loading="lazy" decoding="async">
@elseif (! ($imageOnly ?? false))
    <span class="{{ $fallbackClass ?? 'topbar__mark' }}" aria-hidden="true">SJ</span>
@endif
