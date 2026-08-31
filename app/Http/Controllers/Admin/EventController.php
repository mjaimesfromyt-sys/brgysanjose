<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with('facility')->orderBy('start_date', 'desc')->get();
        $facilities = Facility::where('is_active', true)->orderBy('name')->get();
        return view('admin.events.index', compact('events', 'facilities'));
    }

    public function store(Request $request)
    {
        $data = $this->validateEvent($request);
        $data['created_by'] = $request->user()->id;

        Event::create($data);
        return back()->with('success', 'Event created.');
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return back()->with('success', 'Event deleted.');
    }

    private function validateEvent(Request $request): array
    {
        $validated = $request->validate([
            'title'           => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string', 'max:1000'],
            'start_date'      => ['required', 'date'],
            'end_date'        => ['required', 'date', 'after_or_equal:start_date'],
            'start_time'      => ['nullable', 'date_format:H:i'],
            'end_time'        => ['nullable', 'date_format:H:i', 'after:start_time'],
            'facility_id'     => ['nullable', 'exists:facilities,id'],
            'blocks_facility' => ['nullable', 'boolean'],
        ]);

        $blocks = $request->boolean('blocks_facility');

        // A blocking event must have a facility AND a time window
        if ($blocks && (empty($validated['facility_id']) || empty($validated['start_time']) || empty($validated['end_time']))) {
            throw ValidationException::withMessages([
                'blocks_facility' => 'To block a facility, choose a facility and set both a start and end time.',
            ]);
        }

        // If this event blocks a facility, ensure it doesn't overlap another blocking event
        // If this event blocks a facility, ensure it doesn't overlap another blocking event OR an existing booking
        if ($blocks) {
            $eventOverlap = Event::overlappingBlockingEvent(
                $validated['facility_id'],
                $validated['start_date'],
                $validated['end_date'],
                $validated['start_time'],
                $validated['end_time']
            )->exists();

            if ($eventOverlap) {
                throw ValidationException::withMessages([
                    'blocks_facility' => 'Another blocking event already occupies this facility for an overlapping date and time.',
                ]);
            }

            $bookingOverlap = \App\Models\Booking::conflicting(
                $validated['facility_id'],
                $validated['start_date'],
                $validated['end_date'],
                $validated['start_time'],
                $validated['end_time']
            )->exists();

            if ($bookingOverlap) {
                throw ValidationException::withMessages([
                    'blocks_facility' => 'An existing booking (pending or approved) already occupies this facility for an overlapping date and time. Resolve that booking before blocking this slot.',
                ]);
            }
        }

        $validated['blocks_facility'] = $blocks;
        return $validated;
    }
}