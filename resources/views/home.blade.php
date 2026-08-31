@extends('layouts.app')
@section('title', 'Barangay San Jose')
@section('full-width', true)

@section('content')
<section class="hero">
    <div class="container">
        {{-- Official letterhead: barangay seal left, municipal seal right,
             matching the layout on the barangay's own certificates. --}}
        <div class="crest">
            @include('partials.seal', [
                'seal' => 'barangay', 'class' => 'crest__seal', 'imageOnly' => true,
            ])

            <div class="crest__text">
                <span class="crest__republic">
                    Republic of the Philippines<br>
                    Province of Bohol &middot; Municipality of Talibon
                </span>
                <h1 class="crest__name">BARANGAY SAN JOSE</h1>
                <span class="crest__since">Talibon, Bohol &middot; Est. 1910</span>
            </div>

            @include('partials.seal', [
                'seal' => 'talibon', 'class' => 'crest__seal', 'imageOnly' => true,
            ])
        </div>

        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <h2 class="hero__title mb-3">Barangay services, without the extra trip.</h2>

                <p class="lead text-muted mb-4" style="max-width:34rem">
                    Community news, upcoming events, and barangay services —
                    reserve a facility, check requirements, or request a document online.
                </p>

                @guest
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Create an account</a>
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-lg">Log in</a>
                    </div>
                    <p class="text-muted mt-3 mb-0">
                        You can read all news and events below without an account.
                    </p>
                @else
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">
                        @include('partials.icon', ['name' => 'dashboard', 'size' => 18])
                        <span class="ms-1">Go to my dashboard</span>
                    </a>
                @endguest
            </div>

            <div class="col-lg-5">
                <div class="card-soft p-4">
                    <h2 class="h6 fw-bold text-muted mb-3" style="text-transform:uppercase;letter-spacing:.07em">
                        Barangay services
                    </h2>

                    <ul class="list-unstyled m-0 d-grid gap-3">
                        @foreach ([
                            ['calendar-check', 'Book a facility', 'Hall, covered court or conference room.'],
                            ['package', 'Rent equipment', 'Chairs, tables, tents and more.'],
                            ['file-text', 'Request documents', 'Clearance, indigency or business permit.'],
                            ['clipboard', 'Check requirements', 'Know what to bring before you travel.'],
                        ] as [$icon, $label, $text])
                            <li class="d-flex gap-3">
                                <span class="action-tile__icon mb-0 flex-shrink-0" style="width:2.5rem;height:2.5rem">
                                    @include('partials.icon', ['name' => $icon, 'size' => 20])
                                </span>
                                <span>
                                    <span class="d-block fw-semibold">{{ $label }}</span>
                                    <span class="text-muted">{{ $text }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container py-5">
    <div class="row g-4">

        {{-- News --}}
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h4 fw-bold mb-0 d-flex align-items-center gap-2">
                    @include('partials.icon', ['name' => 'megaphone', 'size' => 22])
                    Barangay News
                </h2>
                @if ($announcements->isNotEmpty())
                    <a href="{{ route('announcements.index') }}" class="fw-semibold text-decoration-none">
                        View all
                    </a>
                @endif
            </div>

            @if ($announcements->isEmpty())
                <div class="card-soft">
                    <div class="empty">
                        @include('partials.icon', ['name' => 'newspaper', 'size' => 32])
                        <div class="empty__title mt-2">No announcements yet</div>
                        <p class="mb-0">Barangay news and advisories will be posted here.</p>
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

                                <h3 class="news-item__title">{{ $post->title }}</h3>
                                <p class="news-item__excerpt">{{ Str::limit($post->body, 130) }}</p>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Events --}}
        <div class="col-lg-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h4 fw-bold mb-0 d-flex align-items-center gap-2">
                    @include('partials.icon', ['name' => 'calendar', 'size' => 22])
                    Upcoming Events
                </h2>
            </div>

            <div class="card-soft">
                @if ($upcomingEvents->isEmpty())
                    <div class="empty">
                        <div class="empty__title">Nothing scheduled</div>
                        <p class="mb-0">Community events will appear here.</p>
                    </div>
                @else
                    <ul class="list-unstyled m-0 p-2">
                        @foreach ($upcomingEvents as $event)
                            <li class="d-flex gap-3 p-2 align-items-start">
                                <div class="text-center flex-shrink-0" style="min-width:3rem">
                                    <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;font-weight:700">
                                        {{ $event->start_date->format('M') }}
                                    </div>
                                    <div class="fw-bold" style="font-size:1.25rem;line-height:1">
                                        {{ $event->start_date->format('j') }}
                                    </div>
                                </div>
                                <div class="flex-grow-1" style="min-width:0">
                                    <div class="fw-semibold">{{ $event->title }}</div>
                                    @if ($event->start_time)
                                        <div class="text-muted small d-flex align-items-center gap-1">
                                            @include('partials.icon', ['name' => 'clock', 'size' => 14])
                                            {{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }}
                                        </div>
                                    @endif
                                    @if ($event->facility)
                                        <div class="text-muted small d-flex align-items-center gap-1">
                                            @include('partials.icon', ['name' => 'map-pin', 'size' => 14])
                                            {{ $event->facility->name }}
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @auth
                    <div class="border-top p-3">
                        <a href="{{ route('events.calendar') }}" class="btn btn-outline-secondary w-100">
                            @include('partials.icon', ['name' => 'calendar', 'size' => 18])
                            <span class="ms-1">Open full calendar</span>
                        </a>
                    </div>
                @endauth
            </div>

            <div class="card-soft p-3 mt-3">
                <h3 class="h6 fw-bold mb-2">Barangay Hall</h3>
                <p class="text-muted small mb-2 d-flex align-items-start gap-2">
                    @include('partials.icon', ['name' => 'map-pin', 'size' => 16])
                    Purok 5, San Jose, Talibon, Bohol
                </p>
                <p class="text-muted small mb-0 d-flex align-items-start gap-2">
                    @include('partials.icon', ['name' => 'mail', 'size' => 16])
                    blgusanjosetalibon1910@gmail.com
                </p>
            </div>
        </div>

    </div>
</div>
@endsection
