@extends('layouts.app')
@section('title', 'Barangay News')

@section('content')
<div class="mb-4">
    <h1 class="page-title d-flex align-items-center gap-2">
        @include('partials.icon', ['name' => 'megaphone', 'size' => 26])
        Barangay News
    </h1>
    <p class="page-subtitle">Announcements, advisories and notices from Barangay San Jose.</p>
</div>

@if ($announcements->isEmpty())
    <div class="card-soft">
        <div class="empty">
            @include('partials.icon', ['name' => 'newspaper', 'size' => 32])
            <div class="empty__title mt-2">No announcements yet</div>
            <p class="mb-0">Check back soon for barangay news and advisories.</p>
        </div>
    </div>
@else
    <div class="row g-3">
        @foreach ($announcements as $post)
            <div class="col-md-6">
                <a class="news-item" href="{{ route('announcements.show', $post) }}">
                    <div class="news-item__meta">
                        @if ($post->is_pinned)
                            <span class="pill pill--pending">
                                @include('partials.icon', ['name' => 'pin', 'size' => 13])
                                <span class="ms-1">Pinned</span>
                            </span>
                        @endif
                        @if ($post->category)
                            <span class="pill pill--info">{{ $post->category }}</span>
                        @endif
                        <span>{{ $post->display_date->format('M d, Y') }}</span>
                    </div>

                    <h2 class="news-item__title">{{ $post->title }}</h2>
                    <p class="news-item__excerpt">{{ Str::limit($post->body, 160) }}</p>
                </a>
            </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $announcements->links() }}
    </div>
@endif
@endsection
