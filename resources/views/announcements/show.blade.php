@extends('layouts.app')
@section('title', $announcement->title)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        <a href="{{ route('announcements.index') }}"
           class="d-inline-flex align-items-center gap-1 text-decoration-none small fw-semibold mb-3">
            @include('partials.icon', ['name' => 'arrow-left', 'size' => 16])
            All news
        </a>

        <article class="card-soft p-4 p-md-5">
            <div class="news-item__meta">
                @if ($announcement->is_pinned)
                    <span class="pill pill--pending">
                        @include('partials.icon', ['name' => 'pin', 'size' => 13])
                        <span class="ms-1">Pinned</span>
                    </span>
                @endif
                @if ($announcement->category)
                    <span class="pill pill--info">{{ $announcement->category }}</span>
                @endif
                <span class="d-inline-flex align-items-center gap-1">
                    @include('partials.icon', ['name' => 'clock', 'size' => 14])
                    {{ $announcement->display_date->format('F j, Y') }}
                </span>
            </div>

            <h1 class="fw-bold mb-3" style="font-size:clamp(1.6rem,3vw,2.2rem);letter-spacing:-.02em">
                {{ $announcement->title }}
            </h1>

            <div class="prose">{{ $announcement->body }}</div>

            @if ($announcement->author)
                <hr class="my-4">
                <p class="text-muted mb-0 d-flex align-items-center gap-2">
                    @include('partials.icon', ['name' => 'user', 'size' => 16])
                    Posted by {{ $announcement->author->name }}
                </p>
            @endif
        </article>

    </div>
</div>
@endsection
