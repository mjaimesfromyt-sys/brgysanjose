@extends('layouts.app')
@section('title', 'Events Calendar')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="page-title d-flex align-items-center gap-2">
            @include('partials.icon', ['name' => 'calendar', 'size' => 26])
            Community Events
        </h1>
        <p class="page-subtitle">Barangay activities and facility schedules.</p>
    </div>

    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('events.calendar', ['month' => $prev]) }}"
           class="btn btn-outline-secondary" aria-label="Previous month">&larr;</a>
        <span class="fw-bold px-2" style="min-width:11rem;text-align:center">
            {{ $cursor->format('F Y') }}
        </span>
        <a href="{{ route('events.calendar', ['month' => $next]) }}"
           class="btn btn-outline-secondary" aria-label="Next month">&rarr;</a>
    </div>
</div>

<div class="card-soft p-2 p-md-3">
    {{-- Weekday header. Hidden on narrow screens where cells stack 2-up. --}}
    <div class="row row-cols-7 g-0 text-center cal-head pb-2 mb-2 border-bottom d-none d-md-flex">
        <div class="col">Sun</div><div class="col">Mon</div><div class="col">Tue</div>
        <div class="col">Wed</div><div class="col">Thu</div><div class="col">Fri</div><div class="col">Sat</div>
    </div>

    <div class="row row-cols-2 row-cols-md-7 g-2">
        @foreach ($days as $day)
            <div class="col">
                <div class="cal-cell h-100 {{ $day['inMonth'] ? '' : 'is-outside' }} {{ $day['isToday'] ? 'is-today' : '' }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="cal-date {{ $day['isToday'] ? 'cal-date--today' : '' }}">
                            {{ $day['date']->format('j') }}
                        </span>
                        {{-- Weekday shown inline on mobile, where the header row is hidden --}}
                        <span class="text-muted d-md-none" style="font-size:.72rem">
                            {{ $day['date']->format('D') }}
                        </span>
                    </div>

                    @foreach ($day['events'] as $event)
                        <span class="cal-event {{ $event->blocks_facility ? 'cal-event--blocking' : 'cal-event--info' }}"
                              title="{{ $event->title }}@if($event->facility) @ {{ $event->facility->name }}@endif">
                            {{ $event->title }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="d-flex flex-wrap gap-3 mt-3 align-items-center">
    <span class="d-inline-flex align-items-center gap-2">
        <span class="cal-event cal-event--info mt-0" style="width:1.75rem">&nbsp;</span>
        <span class="text-muted">Community event</span>
    </span>
    <span class="d-inline-flex align-items-center gap-2">
        <span class="cal-event cal-event--blocking mt-0" style="width:1.75rem">&nbsp;</span>
        <span class="text-muted">Blocks a facility</span>
    </span>
</div>
@endsection
