@extends('layouts.admin')
@section('title', 'Edit announcement')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="mb-4">
            <a href="{{ route('admin.announcements.index') }}"
               class="d-inline-flex align-items-center gap-1 text-decoration-none small fw-semibold">
                @include('partials.icon', ['name' => 'arrow-left', 'size' => 16])
                All announcements
            </a>
            <h1 class="page-title mt-2">Edit announcement</h1>
            <p class="page-subtitle">Changes appear on the public homepage immediately once published.</p>
        </div>

        <div class="card-soft p-4">
            @include('admin.announcements._form', [
                'action' => route('admin.announcements.update', $announcement),
                'post'   => $announcement,
            ])
        </div>

        @if ($announcement->is_published)
            <div class="mt-3">
                <a href="{{ route('announcements.show', $announcement) }}"
                   class="d-inline-flex align-items-center gap-1 text-decoration-none fw-semibold">
                    @include('partials.icon', ['name' => 'eye', 'size' => 16])
                    View this announcement on the public site
                </a>
            </div>
        @endif

    </div>
</div>
@endsection
