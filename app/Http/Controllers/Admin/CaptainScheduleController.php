<?php

namespace App\Http\Controllers\Admin;
use App\Events\ResidentStatusUpdatedEvent;

use App\Http\Controllers\Controller;
use App\Models\CaptainAppointment;
use App\Models\CaptainUnavailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CaptainScheduleController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');

        $query = CaptainAppointment::with('user')->orderBy('date', 'desc')->orderBy('start_time', 'asc');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $appointments = $query->paginate(15);

        $unavailabilities = CaptainUnavailability::whereDate('date', '>=', Carbon::today())
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        return view('admin.appointments.index', compact('appointments', 'unavailabilities', 'status'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_remarks' => 'nullable|string|max:500',
        ]);

        $appointment = CaptainAppointment::findOrFail($id);
        $appointment->status = $request->status;
        $appointment->reviewed_by = Auth::id();
        $appointment->reviewed_at = Carbon::now();

        if ($request->status === 'rejected') {
            $appointment->admin_remarks = $request->admin_remarks;
        }

        $appointment->save();
        event(new ResidentStatusUpdatedEvent($appointment->user_id, 'Captain Appointment Update', 'Appointment', ucfirst($request->status), 'Your appointment request has been ' . strtolower($request->status) . '.', route('appointments.index')));


        activity('appointments')
            ->causedBy(Auth::user())
            ->performedOn($appointment)
            ->withProperties([
                'action' => $request->status,
                'appointment_id' => $appointment->id,
                'resident_id' => $appointment->user_id,
            ])
            ->log('Captain appointment ' . $request->status);
        return redirect()->back()->with('success', 'Appointment status successfully updated.');
    }

    public function storeUnavailability(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'reason' => 'required|string|max:255',
        ]);

        CaptainUnavailability::create([
            'date' => $request->date,
            'start_time' => Carbon::parse($request->start_time)->format('H:i:s'),
            'end_time' => Carbon::parse($request->end_time)->format('H:i:s'),
            'reason' => $request->reason,
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Schedule block / lakaw ni Kapitan successfully saved.');
    }

    public function destroyUnavailability($id)
    {
        $unavailability = CaptainUnavailability::findOrFail($id);
        $unavailability->delete();

        return redirect()->back()->with('success', 'Schedule block successfully removed.');
    }
}