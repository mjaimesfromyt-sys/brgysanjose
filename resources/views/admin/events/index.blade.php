@extends('layouts.admin')
@section('title', 'Events')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="page-title">Events</h1>
        <p class="page-subtitle">Publish community events, and block facilities when an event occupies them.</p>
    </div>
    <a href="{{ route('events.calendar') }}" class="btn btn-outline-secondary">View calendar</a>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card-soft h-100">
            <div class="p-3 border-bottom">
                <h2 class="h6 mb-0 fw-bold">All events</h2>
            </div>

            @if ($events->isEmpty())
                <div class="empty">
                    <div class="empty__title">No events yet</div>
                    <p class="mb-0">Add your first community event using the form.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Dates</th>
                                <th>Facility</th>
                                <th>Blocking</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($events as $event)
                                <tr>
                                    <td class="fw-semibold">{{ $event->title }}</td>
                                    <td>
                                        @if ($event->start_date->eq($event->end_date))
                                            {{ $event->start_date->format('M d, Y') }}
                                        @else
                                            {{ $event->start_date->format('M d') }} –
                                            {{ $event->end_date->format('M d, Y') }}
                                        @endif
                                        @if ($event->start_time)
                                            <div class="text-muted small">
                                                {{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }} –
                                                {{ \Carbon\Carbon::parse($event->end_time)->format('g:i A') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $event->facility->name ?? '—' }}</td>
                                    <td>
                                        @if ($event->blocks_facility)
                                            <span class="pill pill--pending">Blocks</span>
                                        @else
                                            <span class="pill pill--neutral">No</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('admin.events.destroy', $event) }}"
                                              onsubmit="return confirm('Delete this event?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
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
                <h2 class="h6 mb-0 fw-bold">Add event</h2>
            </div>

            <div class="p-3">
                <form method="POST" action="{{ route('admin.events.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input id="title" name="title" value="{{ old('title') }}"
                               class="form-control @error('title') is-invalid @enderror" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" rows="2"
                                  class="form-control">{{ old('description') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="start_date" class="form-label">Start date</label>
                            <input id="start_date" type="date" name="start_date" value="{{ old('start_date') }}"
                                   class="form-control @error('start_date') is-invalid @enderror" required>
                            @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-6 mb-3">
                            <label for="end_date" class="form-label">End date</label>
                            <input id="end_date" type="date" name="end_date" value="{{ old('end_date') }}"
                                   class="form-control @error('end_date') is-invalid @enderror" required>
                            @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="start_time" class="form-label">
                                Start time <span class="text-muted fw-normal">(opt)</span>
                            </label>
                            <input id="start_time" type="time" name="start_time" value="{{ old('start_time') }}"
                                   class="form-control @error('start_time') is-invalid @enderror">
                            @error('start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-6 mb-3">
                            <label for="end_time" class="form-label">
                                End time <span class="text-muted fw-normal">(opt)</span>
                            </label>
                            <input id="end_time" type="time" name="end_time" value="{{ old('end_time') }}"
                                   class="form-control @error('end_time') is-invalid @enderror">
                            @error('end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="facility_id" class="form-label">
                            Facility <span class="text-muted fw-normal">(opt)</span>
                        </label>
                        <select id="facility_id" name="facility_id" class="form-select">
                            <option value="">— None —</option>
                            @foreach ($facilities as $facility)
                                <option value="{{ $facility->id }}" {{ old('facility_id') == $facility->id ? 'selected' : '' }}>
                                    {{ $facility->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="blocks_facility" id="blocks_facility" value="1"
                               class="form-check-input @error('blocks_facility') is-invalid @enderror"
                               {{ old('blocks_facility') ? 'checked' : '' }}>
                        <label for="blocks_facility" class="form-check-label">
                            Block this facility (prevents resident bookings during the event)
                        </label>
                        @error('blocks_facility') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <button class="btn btn-primary w-100">Add event</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
