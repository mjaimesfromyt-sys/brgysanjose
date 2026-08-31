<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Facility;
use App\Notifications\BookingStatusNotification;
use App\Services\PayMongoService;
use App\Support\ClaimCode;
use App\Support\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function __construct(private readonly PayMongoService $payMongo)
    {
    }

    public function index(Request $request)
    {
        $bookings = $request->user()->bookings()
            ->with('facility')
            ->latest('start_date')
            ->get();

        return view('bookings.index', compact('bookings'));
    }

    public function create(Request $request)
    {
        $this->ensureActive($request);

        $facilities = Facility::where('is_active', true)->orderBy('name')->get();
        return view('bookings.create', compact('facilities'));
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

        $conflict = Booking::conflicting(
            $validated['facility_id'],
            $validated['start_date'],
            $validated['end_date'],
            $validated['start_time'],
            $validated['end_time']
        )->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'start_date' => 'This facility is already booked for an overlapping date and time. Please choose another slot.',
            ]);
        }

        $eventConflict = Event::blockingConflict(
            $validated['facility_id'],
            $validated['start_date'],
            $validated['end_date'],
            $validated['start_time'],
            $validated['end_time']
        )->exists();

        if ($eventConflict) {
            throw ValidationException::withMessages([
                'start_date' => 'This facility is reserved for an official barangay event during the selected period. Please choose another slot.',
            ]);
        }

        $facility = Facility::findOrFail($validated['facility_id']);
        $amountDue = $facility->fee ?? 0;
        $isCashless = $validated['payment_method'] !== 'cash';

        if ($isCashless) {
            $amountDue += PayMongoService::transactionFee();
        }

        $booking = $request->user()->bookings()->create($validated + [
            'status'         => 'pending',
            'amount_due'     => $amountDue,
            'payment_status' => 'unpaid',
        ]);

        if (! $isCashless) {
            return redirect()->route('bookings.index')
                ->with('success', 'Booking request submitted. It is now pending admin approval.');
        }

        return $this->startCheckout(
            $booking,
            [
                [
                    'name'     => $facility->name,
                    'amount'   => PayMongoService::toCentavos($facility->fee ?? 0),
                    'currency' => 'PHP',
                    'quantity' => 1,
                ],
                PayMongoService::transactionFeeLineItem(),
            ],
            "Facility booking #{$booking->id} — Barangay San Jose",
            route('bookings.pay.callback', $booking),
            route('bookings.pay.cancel', $booking),
        );
    }

    public function paymentCallback(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        $this->confirmPayment($booking);

        if ($booking->payment_status === 'paid') {
            return redirect()->route('bookings.receipt', $booking)
                ->with('success', 'Payment confirmed. Here is your receipt and claim code.');
        }

        return redirect()->route('bookings.index')
            ->with('error', 'We could not confirm your payment yet. If you completed it, check back shortly — otherwise you can try paying again.');
    }

    public function paymentCancelled(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        return redirect()->route('bookings.index')
            ->with('error', 'Payment was not completed. You can try paying again from My Bookings.');
    }

    public function retryPayment(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === $request->user()->id, 403);
        abort_if($booking->payment_status === 'paid', 400);
        abort_if($booking->payment_method === 'cash', 400);

        $booking->load('facility');

        return $this->startCheckout(
            $booking,
            [
                [
                    'name'     => $booking->facility->name,
                    'amount'   => PayMongoService::toCentavos($booking->facility->fee ?? 0),
                    'currency' => 'PHP',
                    'quantity' => 1,
                ],
                PayMongoService::transactionFeeLineItem(),
            ],
            "Facility booking #{$booking->id} — Barangay San Jose",
            route('bookings.pay.callback', $booking),
            route('bookings.pay.cancel', $booking),
        );
    }

    public function receipt(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === $request->user()->id, 403);
        abort_unless($booking->claim_code, 404);

        $booking->load('facility');

        $lines = [
            ['label' => $booking->facility->name, 'value' => '₱' . number_format($booking->facility->fee ?? 0, 2)],
        ];

        if ($booking->payment_method !== 'cash') {
            $lines[] = ['label' => 'Transaction Fee', 'value' => '₱' . number_format(PayMongoService::transactionFee(), 2)];
        }

        $receipt = [
            'title'            => 'Facility Booking Receipt',
            'claimCode'        => $booking->claim_code,
            'residentName'     => $request->user()->name,
            'date'             => $booking->created_at,
            'lines'            => $lines,
            'amount'           => $booking->amount_due,
            'paymentMethod'    => $booking->payment_method,
            'paymentChannel'   => $booking->payment_channel,
            'paymentReference' => $booking->payment_reference,
            'note'             => 'Present this receipt and claim code to the barangay hall once your booking is approved.',
            'backRoute'        => route('bookings.index'),
        ];

        return view('receipts.show', compact('receipt'));
    }

    private function startCheckout(
        Booking $record,
        array $lineItems,
        string $description,
        string $successUrl,
        string $cancelUrl,
    ) {
        try {
            $session = $this->payMongo->createCheckoutSession(
                $lineItems,
                PayMongoService::methodTypesFor($record->payment_method),
                $description,
                $successUrl,
                $cancelUrl,
                "booking-{$record->id}",
            );
        } catch (\Throwable $e) {
            Log::error('PayMongo checkout session creation failed', ['error' => $e->getMessage(), 'booking_id' => $record->id]);

            return redirect()->route('bookings.index')
                ->with('error', 'We could not start the online payment right now. Please try again in a moment, or choose Cash instead.');
        }

        $record->update(['paymongo_checkout_session_id' => $session['id']]);

        return redirect()->away($session['checkout_url']);
    }

    private function confirmPayment(Booking $booking): void
    {
        if ($booking->payment_status === 'paid' || ! $booking->paymongo_checkout_session_id) {
            return;
        }

        try {
            $session = $this->payMongo->retrieveCheckoutSession($booking->paymongo_checkout_session_id);
        } catch (\Throwable $e) {
            Log::error('PayMongo checkout session lookup failed', ['error' => $e->getMessage(), 'booking_id' => $booking->id]);

            return;
        }

        if ($this->payMongo->isPaid($session)) {
            $booking->update([
                'payment_status'    => 'paid',
                'payment_channel'   => $this->payMongo->paidChannel($session),
                'payment_reference' => $this->payMongo->paidReference($session),
                'claim_code'        => ClaimCode::next('bookings'),
            ]);

            $booking->load('user', 'facility');
            Notify::send($booking->user, new BookingStatusNotification($booking, 'payment_confirmed'));
        }
    }

    private function ensureActive(Request $request): void
    {
        if (! $request->user()->isActive()) {
            abort(403, 'Your account is still pending review by the barangay. You cannot make bookings yet.');
        }
    }
}
