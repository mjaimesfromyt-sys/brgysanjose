<?php
namespace App\Http\Controllers;

use App\Events\NewTransactionEvent;
use App\Models\CaptainAppointment;
use App\Models\CaptainUnavailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
class CaptainAppointmentController extends Controller {
    public function index() {
        $appointments = CaptainAppointment::where("user_id", Auth::id())->orderBy("date", "desc")->orderBy("start_time", "desc")->paginate(10);
        return view("appointments.index", compact("appointments"));
    }
    public function create() {
        return view("appointments.create");
    }
    public function slots(Request $request) {
        $date = $request->input("date", Carbon::today()->toDateString());
        $unavailabilities = CaptainUnavailability::where("date", $date)->get()->map(function ($item) {
            return ["type" => "unavailability", "title" => "Official Business — " . ($item->reason ?? "Out of Office"), "start_time" => Carbon::parse($item->start_time)->format("h:i A"), "end_time" => Carbon::parse($item->end_time)->format("h:i A"), "start_raw" => Carbon::parse($item->start_time)->format("H:i"), "end_raw" => Carbon::parse($item->end_time)->format("H:i"), "badge" => "Unavailable", "color" => "danger"];
        });
        $booked = CaptainAppointment::where("date", $date)->whereIn("status", ["approved", "pending"])->get()->map(function ($item) {
            return ["type" => "appointment", "title" => "Reserved Appointment (" . $item->category . ")", "start_time" => Carbon::parse($item->start_time)->format("h:i A"), "end_time" => Carbon::parse($item->end_time)->format("h:i A"), "start_raw" => Carbon::parse($item->start_time)->format("H:i"), "end_raw" => Carbon::parse($item->end_time)->format("H:i"), "badge" => "Reserved", "color" => "warning"];
        });
        $slots = $unavailabilities->concat($booked);
        return response()->json(["date" => $date, "slots" => $slots, "status" => $slots->isEmpty() ? "Available" : "Partially Booked"]);
    }
    public function store(Request $request) {
        $request->validate([
            "start_time" => ["required", "date_format:H:i", function ($attribute, $value, $fail) {
                if ($value < "08:00" || $value > "16:00") {
                    $fail("Appointments are strictly accepted between 8:00 AM and 5:00 PM only.");
                }
            }],"appointment_date" => "required|date|after_or_equal:today", "start_time" => "required", "end_time" => "required|after:start_time", "category" => "required|string|max:100", "reason" => "required|string|min:5|max:1000"]);
        $date = $request->appointment_date;
        $start = Carbon::parse($request->start_time)->format("H:i:s");
        $end = Carbon::parse($request->end_time)->format("H:i:s");
        $conflict = CaptainUnavailability::where("date", $date)->where(function ($q) use ($start, $end) { $q->where("start_time", "<", $end)->where("end_time", ">", $start); })->first();
        if ($conflict) { return back()->withInput()->withErrors(["start_time" => "Schedule conflict: Kapitan is unavailable during these hours."]); }
        $bookedConflict = CaptainAppointment::where("date", $date)->whereIn("status", ["approved", "pending"])->where(function ($q) use ($start, $end) { $q->where("start_time", "<", $end)->where("end_time", ">", $start); })->first();
        if ($bookedConflict) { return back()->withInput()->withErrors(["start_time" => "Schedule conflict: An appointment already exists in this slot."]); }
        $appointment = CaptainAppointment::create(["user_id" => Auth::id(), "date" => $date, "start_time" => $start, "end_time" => $end, "category" => $request->category, "reason" => $request->reason, "status" => "pending"]);
        activity("appointments")
            ->causedBy(Auth::user())
            ->withProperties(["category" => $request->category, "date" => $date, "time" => $start . " - " . $end])
            ->log("Captain appointment request submitted");
        event(new NewTransactionEvent(
            'Captain Appointment',
            'New Captain Appointment Request',
            auth()->user()->name ?? 'Resident',
            'APT-' . ($appointment->id ?? rand(100, 999)),
            route('admin.appointments.index')
        ));

        return redirect()->route("appointments.index")->with("success", "Appointment submitted successfully!");
    }
}
