<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EventCalendarController extends Controller
{
    public function index(Request $request)
    {
        // Which month to show (default current)
        $month = $request->query('month');
        $cursor = $month ? Carbon::createFromFormat('Y-m', $month)->startOfMonth() : Carbon::now()->startOfMonth();

        $startOfMonth = $cursor->copy()->startOfMonth();
        $endOfMonth   = $cursor->copy()->endOfMonth();

        // Grid starts on the Sunday on/before the 1st, ends on Saturday on/after the last day
        $gridStart = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $gridEnd   = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);

        // Events overlapping this month
        $events = Event::with('facility')
            ->where('start_date', '<=', $endOfMonth)
            ->where('end_date', '>=', $startOfMonth)
            ->orderBy('start_time')
            ->get();

        // Build the day grid
        $days = [];
        for ($d = $gridStart->copy(); $d <= $gridEnd; $d->addDay()) {
            $dayEvents = $events->filter(function ($e) use ($d) {
                return $d->betweenIncluded($e->start_date, $e->end_date);
            })->values();

            $days[] = [
                'date'         => $d->copy(),
                'inMonth'      => $d->month === $cursor->month,
                'isToday'      => $d->isToday(),
                'events'       => $dayEvents,
            ];
        }

        return view('events.calendar', [
            'cursor'   => $cursor,
            'days'     => $days,
            'prev'     => $cursor->copy()->subMonth()->format('Y-m'),
            'next'     => $cursor->copy()->addMonth()->format('Y-m'),
        ]);
    }
}