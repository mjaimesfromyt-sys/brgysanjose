@extends('layouts.admin')
@section('title', 'Announcements')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="page-title">Announcements</h1>
        <p class="page-subtitle">Post barangay news to the public homepage. Only admins can publish.</p>
    </div>
    <a href="{{ route('home') }}" class="btn btn-outline-secondary">
        @include('partials.icon', ['name' => 'eye', 'size' => 18])
        <span class="ms-1">View public page</span>
    </a>
</div>

@php
    $hidden = $announcements->filter(
        fn ($a) => ! $a->is_published || ($a->published_at && $a->published_at->isFuture())
    );
@endphp

@if ($hidden->isNotEmpty())
    <div class="alert alert-warning" role="alert">
        <strong>
            {{ $hidden->count() }}
            {{ Str::plural('announcement', $hidden->count()) }}
            {{ $hidden->count() === 1 ? 'is' : 'are' }} not visible to the public.
        </strong>
        <ul class="mb-0 mt-1 ps-3">
            @foreach ($hidden as $h)
                <li>
                    “{{ $h->title }}” —
                    @if (! $h->is_published)
                        saved as a <strong>draft</strong>
                    @else
                        <strong>scheduled</strong> for {{ $h->published_at->format('M j, Y') }}
                    @endif
                </li>
            @endforeach
        </ul>
        <p class="mb-0 mt-2">Use <strong>Publish now</strong> below to put one live immediately.</p>
    </div>
@endif

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card-soft h-100">
            <div class="p-3 border-bottom">
                <h2 class="h6 mb-0 fw-bold">All announcements</h2>
            </div>

            @if ($announcements->isEmpty())
                <div class="empty">
                    @include('partials.icon', ['name' => 'newspaper', 'size' => 32])
                    <div class="empty__title mt-2">Nothing posted yet</div>
                    <p class="mb-0">Use the form to publish your first announcement.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Visibility</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($announcements as $post)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $post->title }}</div>
                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                            @if ($post->is_pinned)
                                                <span class="pill pill--pending">Pinned</span>
                                            @endif
                                            @if ($post->category)
                                                <span class="pill pill--info">{{ $post->category }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-muted">{{ $post->display_date->format('M d, Y') }}</td>
                                    <td>
                                        @if (! $post->is_published)
                                            <span class="pill pill--neutral">Draft</span>
                                        @elseif ($post->published_at && $post->published_at->isFuture())
                                            <span class="pill pill--pending">Scheduled</span>
                                        @else
                                            <span class="pill pill--approved">Published</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex gap-2 justify-content-end flex-wrap">
                                            @if (! $post->is_published || ($post->published_at && $post->published_at->isFuture()))
                                                <form method="POST" action="{{ route('admin.announcements.publish', $post) }}">
                                                    @csrf
                                                    <button class="btn btn-sm btn-success">Publish now</button>
                                                </form>
                                            @endif
                                            <a href="{{ route('admin.announcements.edit', $post) }}"
                                               class="btn btn-sm btn-outline-secondary">Edit</a>
                                            <form method="POST" action="{{ route('admin.announcements.destroy', $post) }}"
                                                  onsubmit="return confirm('Delete this announcement?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card-soft h-100">
            <div class="p-3 border-bottom">
                <h2 class="h6 mb-0 fw-bold d-flex align-items-center gap-2">
                    @include('partials.icon', ['name' => 'megaphone', 'size' => 18])
                    New announcement
                </h2>
            </div>

            <div class="p-3">
                @include('admin.announcements._form', [
                    'action' => route('admin.announcements.store'),
                    'post'   => null,
                ])
            </div>
        </div>
    </div>
</div>
@endsection
