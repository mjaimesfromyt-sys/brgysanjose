@extends('layouts.app')
@section('title', 'Transaction Requirements')

@section('content')
<div class="mb-4">
    <h1 class="page-title d-flex align-items-center gap-2">
        @include('partials.icon', ['name' => 'clipboard', 'size' => 26])
        Transactions &amp; Requirements
    </h1>
    <p class="page-subtitle">Check exactly what to bring before visiting the barangay hall.</p>
</div>

<div class="row g-3">
    @foreach ($types as $type)
        <div class="col-md-6 col-lg-4">
            <a class="action-tile" href="{{ route('info.show', $type) }}">
                <span class="action-tile__icon">
                    @include('partials.icon', ['name' => 'file-text', 'size' => 24])
                </span>
                <h2 class="action-tile__title">{{ $type->name }}</h2>
                <p class="action-tile__text">{{ $type->description }}</p>

                <div class="d-flex flex-wrap gap-2 mt-3">
                    @if ($type->requires_residency)
                        <span class="pill pill--info">Residents only</span>
                    @else
                        <span class="pill pill--neutral">Open to all</span>
                    @endif

                    @if (! is_null($type->fee))
                        <span class="pill {{ $type->fee > 0 ? 'pill--neutral' : 'pill--approved' }}">
                            @if ($type->fee > 0) ₱{{ number_format($type->fee, 2) }} @else Free @endif
                        </span>
                    @endif
                </div>

                <span class="action-tile__cta">
                    {{ $type->requirements_count }} {{ Str::plural('requirement', $type->requirements_count) }}
                    @include('partials.icon', ['name' => 'arrow-right', 'size' => 16])
                </span>
            </a>
        </div>
    @endforeach
</div>
@endsection
