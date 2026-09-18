<?php

namespace App\Http\Controllers;
use App\Events\NewTransactionEvent;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Facility;
use App\Services\PayMongoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function __construct(private readonly PayMongoService $payMongo)
    {
    }

    public function index()
    {
        $bookings = auth()->user()->bookings()
            ->with(['facility'])
            ->latest('id')
            ->paginate(10);

        
        event(new NewTransactionEvent('Facility Booking', 'New Facility Booking', auth()->user()->name ?? 'Resident', 'BK-' . ($booking->id ?? rand(100, 999)), route('admin.bookings.index')));
        return view('bookings.index', compact('bookings'));
    }

    public function create()
    {
        $this->ensureActive(request());
        $facilities = Facility::where('is_active', true)->get();

        return view('bookings.create', compact('facilities'));
    }

    // 👉 LIVE SCHEDULE & TIME SLOT AVAILABILITY API
    public function checkSchedule(Request $request)
    {
        $facilityId = $request->input('facility_id');
        $date = $request->input('date', now()->format('Y-m-d'));

        if (!$facilityId) {
            $first = Facility::first();
            $facilityId = $first ? $first->id : 1;
        }

        // Active bookings for this facility on this date
        $bookings = Booking::where('facility_id', $facilityId)
            ->whereIn('status', ['pending', 'approved'])
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->get(['id', 'start_time', 'end_time', 'purpose', 'status']);

        // Official Barangay Events that block the facility
        $events = Event::where('blocks_facility', true)
            ->where('facility_id', $facilityId)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->get(['id', 'title', 'start_time', 'end_time']);

        return response()->json([
            'date'     => $date,
            'bookings' => $bookings,
            'events'   => $events,
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureActive($request);

        $validated = $request->validate([
            'facility_id'    => ['required', 'exists:facilities,id'],
            'start_date'     => ['required', 'date', 'after_or_equal:today'],
            'end_date'       => ['required', 'date', 'after_or_equal:start_date'],
            'start_time'     => ['required', 'date_format:H:i'],
            'end_time'       => ['required', 'date_format:H:i', 'after:start_time'],
            'purpose'        => ['required', 'string', 'max:255'],
            'payment_method' => ['required', 'in:cash,gcash,paymaya,bank_transfer'],
        ]);

        // Conflict check with existing bookings
        $conflict = Booking::conflicting(
            $validated['facility_id'],
            $validated['start_date'],
            $validated['end_date'],
            $validated['start_time'],
            $validated['end_time']
        )->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'start_time' => 'This facility is already booked for an overlapping time on the selected date. Please choose another time slot.',
            ]);
        }

        // Conflict check with official barangay events
        $eventConflict = Event::blockingConflict(
            $validated['facility_id'],
            $validated['start_date'],
            $validated['end_date'],
            $validated['start_time'],
            $validated['end_time']
        )->exists();

        if ($eventConflict) {
            throw ValidationException::withMessages([
                'start_time' => 'This facility is reserved for an official barangay event during the selected time. Please choose another slot.',
            ]);
        }

        $facility = Facility::findOrFail($validated['facility_id']);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate   = Carbon::parse($validated['end_date']);
        $daysCount = $startDate->diffInDays($endDate) + 1;

        $startTime  = Carbon::createFromFormat('H:i', $validated['start_time']);
        $endTime    = Carbon::createFromFormat('H:i', $validated['end_time']);
        $hoursDiff  = ceil($startTime->diffInMinutes($endTime) / 60);
        $hoursCount = max($hoursDiff, 1);

        // 👉 TINUOD NGA PRICING SA BARANGAY SAN JOSE:
        // 1. Whole Day Package (8+ hours/day): ₱1,000 flat matag adlaw
        if ($hoursCount >= 8) {
            $wholeDayRate = 1000.00;
            $totalFacilityFee = $daysCount * $wholeDayRate;
            $rateDescription = 'Whole Day Package: ₱1,000.00/day (' . $daysCount . ' day/s)';
        } else {
            // 2. Hourly: Adlawan (6am-6pm = ₱50/hr) vs Gabi-i (6pm-11pm = ₱100/hr)
            $startMin = $startTime->hour * 60 + $startTime->minute;
            $endMin = $endTime->hour * 60 + $endTime->minute;
            $dayHours = 0;
            $nightHours = 0;

            $curr = $startMin;
            while ($curr < $endMin) {
                $next = min($curr + 60, $endMin);
                $durationHr = ($next - $curr) / 60;
                $hourOfDay = intdiv($curr, 60);

                if ($hourOfDay >= 6 && $hourOfDay < 18) {
                    $dayHours += $durationHr;
                } else {
                    $nightHours += $durationHr;
                }
                $curr = $next;
            }

            $dayCost = $dayHours * 50.00;
            $nightCost = $nightHours * 100.00;
            $totalFacilityFee = $daysCount * ($dayCost + $nightCost);

            $descParts = [];
            if ($dayHours > 0) $descParts[] = $dayHours . ' hr(s) Daytime (@₱50/hr)';
            if ($nightHours > 0) $descParts[] = $nightHours . ' hr(s) Nighttime w/ lights (@₱100/hr)';
            $rateDescription = implode(' + ', $descParts) . ' × ' . $daysCount . ' day(s)';
        }

        $amountDue = $totalFacilityFee;
        $isCashless = $validated['payment_method'] !== 'cash';

        if ($isCashless && $amountDue > 0) {
            $amountDue += PayMongoService::transactionFee(); // +₱5.00
        }

        $booking = $request->user()->bookings()->create([
            'facility_id'    => $facility->id,
            'start_date'     => $validated['start_date'],
            'end_date'       => $validated['end_date'],
            'start_time'     => $validated['start_time'],
            'end_time'       => $validated['end_time'],
            'purpose'        => $validated['purpose'],
            'status'         => 'pending',
            'payment_method' => $validated['payment_method'],
            'amount_due'     => $amountDue,
            'payment_status' => $amountDue > 0 ? 'unpaid' : 'paid',
        ]);

        if (! $isCashless || $amountDue <= 0) {
            return redirect()->route('bookings.index')
                ->with('success', 'Booking submitted successfully! It is now pending approval by the Barangay.');
        }

        $lineItems = [
            [
                'name'     => $facility->name . ' (' . $rateDescription . ')',
                'amount'   => PayMongoService::toCentavos($totalFacilityFee),
                'currency' => 'PHP',
                'quantity' => 1,
            ],
            PayMongoService::transactionFeeLineItem(),
        ];

        return $this->startCheckout($booking, $lineItems, $validated['payment_method']);
    }

    private function startCheckout(Booking $booking, array $lineItems, string $method)
    {
        try {
            $checkout = $this->payMongo->createCheckoutSession([
                'line_items'           => $lineItems,
                'payment_method_types' => ['qrph', 'gcash', 'paymaya'],
                'success_url'          => route('bookings.pay.callback', ['booking' => $booking->id]) . '?status=success',
                'cancel_url'           => route('bookings.pay.cancel', ['booking' => $booking->id]),
                'description'          => 'Facility Booking #' . $booking->id,
            ]);

            $booking->update([
                'payment_reference' => $checkout['id'] ?? null,
            ]);

            return redirect()->away($checkout['checkout_url']);
        } catch (\Throwable $e) {
            Log::error('PayMongo Checkout Error: ' . $e->getMessage());
            return redirect()->route('bookings.index')
                ->with('warning', 'Booking submitted, but online payment session could not be created. You can pay via cash at the hall.');
        }
    }

    public function retryPayment(Booking $booking)
    {
        if ($booking->payment_status === 'paid') {
            return redirect()->route('bookings.index')->with('info', 'This booking has already been paid.');
        }

        $facility = $booking->facility;
        $totalFacilityFee = max($booking->amount_due - PayMongoService::transactionFee(), 0);

        $lineItems = [
            [
                'name'     => ($facility->name ?? 'Barangay Facility') . ' Reservation',
                'amount'   => PayMongoService::toCentavos($totalFacilityFee),
                'currency' => 'PHP',
                'quantity' => 1,
            ],
            PayMongoService::transactionFeeLineItem(),
        ];

        return $this->startCheckout($booking, $lineItems, $booking->payment_method ?? 'gcash');
    }

    public function paymentCallback(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        // PayMongo redirects the payer here. Never trust the redirect alone —
        // ask PayMongo's API whether the checkout session was actually paid.
        $this->verifyAndConfirm($booking);

        if ($booking->fresh()->payment_status === 'paid') {
            return redirect()->route('bookings.index')
                ->with('success', 'Payment successful! Your reservation is pending verification.');
        }

        return redirect()->route('bookings.index')
            ->with('warning', 'We could not confirm your payment yet. If you already paid, the confirmation usually arrives within a few minutes.');
    }

    /**
     * Re-check the checkout session with PayMongo, then confirm through the
     * shared PaymentConfirmer (same idempotent path the webhook uses).
     */
    private function verifyAndConfirm(Booking $booking): void
    {
        if ($booking->payment_status === 'paid') {
            return; // Webhook may have beaten us here.
        }

        $sessionId = $booking->paymongo_checkout_session_id
            ?: (str_starts_with((string) $booking->payment_reference, 'cs_') ? $booking->payment_reference : null);

        if (! $sessionId) {
            return;
        }

        try {
            $session = $this->payMongo->retrieveCheckoutSession($sessionId);

            if ($this->payMongo->isPaid($session)) {
                app(\App\Services\PaymentConfirmer::class)->confirm(
                    $booking,
                    (string) ($this->payMongo->paidChannel($session) ?? 'qrph'),
                    (string) ($this->payMongo->paidReference($session) ?? ''),
                );
            }
        } catch (\Throwable $e) {
            Log::error('PayMongo callback verification failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

        public function paymentCancelled(Booking $booking)
    {
        return redirect()->route('bookings.index')
            ->with('info', 'Payment was cancelled. You can retry paying anytime by clicking "Pay now" on your booking.');
    }

    public function paymentCancel(Booking $booking)
    {
        return $this->paymentCancelled($booking);
    }

    public function receipt(Booking $booking)
    {
        return view('bookings.receipt', compact('booking'));
    }

    private function ensureActive(Request $request): void
    {
        if (! $request->user()->isActive()) {
            abort(403, 'Your account is pending verification.');
        }
    }
}
