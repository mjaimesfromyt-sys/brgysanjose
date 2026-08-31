<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Notifications\BookingStatusNotification;
use App\Support\ClaimCode;
use App\Support\Notify;
use Illuminate\Http\Request;

class BookingApprovalController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $bookings = Booking::with(['user', 'facility'])
            ->when(in_array($status, ['pending', 'approved', 'rejected']),
                fn ($q) => $q->where('status', $status))
            ->orderBy('start_date')
            ->get();

        $counts = [
            'pending'  => Booking::where('status', 'pending')->count(),
            'approved' => Booking::where('status', 'approved')->count(),
            'rejected' => Booking::where('status', 'rejected')->count(),
        ];

        return view('admin.bookings.index', compact('bookings', 'status', 'counts'));
    }

    public function approve(Request $request, Booking $booking)
    {
        if ($booking->status !== 'pending') {
            return back()->with('error', 'This booking has already been reviewed.');
        }

        // Re-check for conflicts at approval time — another booking may have been
        // approved for an overlapping slot since this request was submitted.
        $conflict = Booking::conflicting(
            $booking->facility_id,
            $booking->start_date->format('Y-m-d'),
            $booking->end_date->format('Y-m-d'),
            $booking->start_time,
            $booking->end_time,
            $booking->id
        )->where('status', 'approved')->exists();

        if ($conflict) {
            return back()->with('error', 'Cannot approve: another approved booking now overlaps this slot.');
        }

        $eventConflict = \App\Models\Event::blockingConflict(
            $booking->facility_id,
            $booking->start_date->format('Y-m-d'),
            $booking->end_date->format('Y-m-d'),
            $booking->start_time,
            $booking->end_time
        )->exists();

        if ($eventConflict) {
            return back()->with('error', 'Cannot approve: an official event now blocks this facility for the requested period.');
        }

        $booking->update([
            'status'      => 'approved',
            'reviewed_by' => $request->user()->id,
            'claim_code'  => $booking->claim_code ?? ClaimCode::next('bookings'),
        ]);

        $booking->load('user', 'facility');
        Notify::send($booking->user, new BookingStatusNotification($booking, 'approved'));

        return back()->with('success', 'Booking approved.');
    }

    public function reject(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'admin_remarks' => ['nullable', 'string', 'max:500'],
        ]);

        if ($booking->status !== 'pending') {
            return back()->with('error', 'This booking has already been reviewed.');
        }

        $booking->update([
            'status'        => 'rejected',
            'reviewed_by'   => $request->user()->id,
            'admin_remarks' => $validated['admin_remarks'] ?? null,
        ]);

        $booking->load('user', 'facility');
        Notify::send($booking->user, new BookingStatusNotification($booking, 'rejected'));

        return back()->with('success', 'Booking rejected.');
    }

    public function markPaid(Booking $booking)
    {
        if ($booking->payment_method !== 'cash' || $booking->payment_status !== 'unpaid') {
            return back()->with('error', 'Only unpaid cash bookings can be marked paid.');
        }

        $booking->update(['payment_status' => 'paid']);

        $booking->load('user', 'facility');
        Notify::send($booking->user, new BookingStatusNotification($booking, 'payment_confirmed'));

        return back()->with('success', 'Booking marked as paid.');
    }
}