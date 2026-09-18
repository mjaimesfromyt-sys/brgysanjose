@extends('layouts.app')
@section('title', 'Events Calendar')

@section('content')
<style>
    body {
        background-color: #f8fafc !important;
    }

    /* 1. Subtle Background Watermark */
    .events-watermark {
        position: fixed;
        top: 55%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 600px;
        height: 600px;
        background-image: url('{{ asset('images/barangay-seal.png') }}');
        background-repeat: no-repeat;
        background-position: center;
        background-size: contain;
        opacity: 0.035;
        pointer-events: none;
        z-index: 0;
    }

    .events-content-wrapper {
        position: relative;
        z-index: 1;
    }

    /* 2. Official Events Hero Card with Seal */
    .events-hero-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 22px 26px;
        box-shadow: 0 4px 20px -4px rgba(0,0,0,0.04);
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }
    .events-hero-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #166534, #22c55e, #3b82f6);
    }

    .brgy-seal-events {
        width: 72px;
        height: 72px;
        object-fit: contain;
        filter: drop-shadow(0 6px 12px rgba(22, 101, 52, 0.16));
        transition: transform 0.25s ease;
    }
    .brgy-seal-events:hover {
        transform: scale(1.08) rotate(3deg);
    }

    /* 3. Modern Calendar Card */
    .calendar-container-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 4px 20px -4px rgba(0,0,0,0.04);
    }

    .month-nav-pill {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 50px;
        padding: 4px 6px;
    }
</style>

<!-- Subtle Watermark sa Background -->
<div class="events-watermark"></div>

<div class="events-content-wrapper">

    <!-- ================= EVENTS HERO HEADER WITH SEAL ================= -->
    <div class="events-hero-card">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <!-- Left: Title & Subtitle -->
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span style="font-size: 24px;">📅</span>
                    <h1 class="h4 fw-bold text-dark mb-0">Community Events &amp; Schedules</h1>
                </div>
                <p class="text-muted small mb-0">Official barangay activities, assemblies, and facility booking schedules for Barangay San Jose.</p>
            </div>

            <!-- Right: Month Nav Controls + Official Barangay Seal -->
            <div class="d-flex align-items-center gap-3 ms-auto flex-wrap">
                <!-- Month Navigator Controls -->
                <div class="month-nav-pill d-flex align-items-center shadow-sm">
                    <a href="{{ route('events.calendar', ['month' => $prev]) }}"
                       class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center" 
                       style="width: 32px; height: 32px; padding: 0;"
                       aria-label="Previous month">&larr;</a>
                    <span class="fw-bold text-dark px-3" style="min-width: 9.5rem; text-align: center; font-size: 13.5px;">
                        {{ $cursor->format('F Y') }}
                    </span>
                    <a href="{{ route('events.calendar', ['month' => $next]) }}"
                       class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center" 
                       style="width: 32px; height: 32px; padding: 0;"
                       aria-label="Next month">&rarr;</a>
                </div>

                <!-- Opisyal nga Barangay Seal -->
                <img src="{{ asset('images/barangay-seal.png') }}" 
                     alt="Barangay San Jose Seal" 
                     class="brgy-seal-events d-none d-sm-block"
                     title="Official Seal of Barangay San Jose, Talibon">
            </div>
        </div>
    </div>

    <!-- ================= CALENDAR GRID CARD ================= -->
    <div class="calendar-container-card">
        <!-- Weekday header (Hidden on narrow screens where cells stack) -->
        <div class="row row-cols-7 g-0 text-center cal-head pb-2 mb-2 border-bottom d-none d-md-flex fw-bold text-muted small text-uppercase" style="letter-spacing: 0.5px;">
            <div class="col text-danger">Sun</div>
            <div class="col">Mon</div>
            <div class="col">Tue</div>
            <div class="col">Wed</div>
            <div class="col">Thu</div>
            <div class="col">Fri</div>
            <div class="col text-success">Sat</div>
        </div>

        <div class="row row-cols-2 row-cols-md-7 g-2">
            @foreach ($days as $day)
                <div class="col">
                    <div class="cal-cell h-100 {{ $day['inMonth'] ? '' : 'is-outside' }} {{ $day['isToday'] ? 'is-today shadow-sm' : '' }}" style="border-radius: 10px; min-height: 90px;">
                        <div class="d-flex align-items-center justify-content-between p-1">
                            <span class="cal-date {{ $day['isToday'] ? 'cal-date--today' : '' }}">
                                {{ $day['date']->format('j') }}
                            </span>
                            <span class="text-muted d-md-none" style="font-size:.72rem">
                                {{ $day['date']->format('D') }}
                            </span>
                        </div>

                        @foreach ($day['events'] as $event)
                            @php
                                $evtTime = '';
                                if ($event->start_time) {
                                    $evtTime = "\n" . \Carbon\Carbon::parse($event->start_time)->format('g:i A');
                                    if ($event->end_time) {
                                        $evtTime .= ' – ' . \Carbon\Carbon::parse($event->end_time)->format('g:i A');
                                    }
                                }
                                $evtFacility = $event->facility ? ' @ ' . $event->facility->name : '';
                                $evtTimeShort = $event->start_time
                                    ? \Carbon\Carbon::parse($event->start_time)->format('g:iA')
                                    : '';
                            @endphp
                            <span class="cal-event {{ $event->blocks_facility ? 'cal-event--blocking' : 'cal-event--info' }} shadow-none"
                                  title="{{ $event->title }}{{ $evtFacility }}{{ $evtTime }}"
                                  style="border-radius: 6px; font-size: 11px;">
                                @if ($evtTimeShort)<strong>{{ $evtTimeShort }}</strong> @endif{{ $event->title }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Legend Footer -->
        <div class="d-flex flex-wrap gap-3 mt-4 pt-3 border-top align-items-center" style="font-size: 12.5px;">
            <span class="d-inline-flex align-items-center gap-2">
                <span class="cal-event cal-event--info mt-0" style="width: 1.5rem; height: 12px; border-radius: 4px;">&nbsp;</span>
                <span class="text-muted fw-semibold">Community event</span>
            </span>
            <span class="d-inline-flex align-items-center gap-2">
                <span class="cal-event cal-event--blocking mt-0" style="width: 1.5rem; height: 12px; border-radius: 4px;">&nbsp;</span>
                <span class="text-muted fw-semibold">Facility Reserved / Occupied</span>
            </span>
        </div>
    </div>

</div>
@endsection