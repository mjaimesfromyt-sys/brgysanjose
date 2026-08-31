<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentRental;
use App\Models\RefundRequest;
use App\Models\User;
use App\Notifications\EquipmentRentalStatusNotification;
use App\Notifications\RefundRequestStatusNotification;
use App\Services\PayMongoService;
use App\Support\ClaimCode;
use App\Support\Notify;
use App\Support\RentalRefund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class EquipmentRentalController extends Controller
{
    public function __construct(private readonly PayMongoService $payMongo)
    {
    }

    public function index(Request $request)
    {
        $rentals = $request->user()->equipmentRentals()
            ->with(['items.equipment', 'refundRequests'])
            ->latest('start_date')
            ->get();

        return view('rentals.index', compact('rentals'));
    }

    public function create(Request $request)
    {
        $this->ensureActive($request);

        $equipment = Equipment::where('is_active', true)->orderBy('name')->get();

        return view('rentals.create', compact('equipment'));
    }

    public function store(Request $request)
    {
        $this->ensureActive($request);

        $validated = $request->validate([
            'start_date'           => ['required', 'date', 'after_or_equal:today'],
            'end_date'             => ['required', 'date', 'after_or_equal:start_date'],
            'purpose'              => ['required', 'string', 'max:255'],
            'items'                => ['required', 'array'],
            'items.*.equipment_id' => ['required', 'exists:equipment,id'],
            'items.*.quantity'     => ['nullable', 'integer', 'min:0'],
            'payment_method'       => ['required', 'in:cash,gcash,paymaya,bank_transfer'],
        ]);

        $lines = collect($validated['items'])
            ->filter(fn ($item) => (int) ($item['quantity'] ?? 0) > 0)
            ->unique('equipment_id')
            ->values();

        if ($lines->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Select at least one item with a quantity greater than zero.',
            ]);
        }

        $equipmentIds = Equipment::where('is_active', true)
            ->whereIn('id', $lines->pluck('equipment_id'))
            ->pluck('id');

        $amountDue = 0;
        $equipmentById = [];

        foreach ($lines as $line) {
            if (! $equipmentIds->contains($line['equipment_id'])) {
                throw ValidationException::withMessages([
                    'items' => 'One or more selected items are no longer available.',
                ]);
            }

            $item = Equipment::findOrFail($line['equipment_id']);
            $available = $item->availableFor($validated['start_date'], $validated['end_date']);

            if ((int) $line['quantity'] > $available) {
                throw ValidationException::withMessages([
                    'items' => "Only {$available} {$item->name}(s) available for the selected dates.",
                ]);
            }

            $equipmentById[$line['equipment_id']] = $item;
            $amountDue += ($item->fee ?? 0) * (int) $line['quantity'];
        }

        $isCashless = $validated['payment_method'] !== 'cash';

        if ($isCashless) {
            $amountDue += PayMongoService::transactionFee();
        }

        $rental = $request->user()->equipmentRentals()->create([
            'start_date'     => $validated['start_date'],
            'end_date'       => $validated['end_date'],
            'purpose'        => $validated['purpose'],
            'status'         => 'pending',
            'payment_method' => $validated['payment_method'],
            'amount_due'     => $amountDue,
            'payment_status' => 'unpaid',
        ]);

        foreach ($lines as $line) {
            $rental->items()->create([
                'equipment_id' => $line['equipment_id'],
                'quantity'     => (int) $line['quantity'],
            ]);
        }

        if (! $isCashless) {
            return redirect()->route('rentals.index')
                ->with('success', 'Equipment rental request submitted. It is now pending admin approval.');
        }

        $lineItems = $lines->map(fn ($line) => [
            'name'     => $equipmentById[$line['equipment_id']]->name,
            'amount'   => PayMongoService::toCentavos($equipmentById[$line['equipment_id']]->fee ?? 0),
            'currency' => 'PHP',
            'quantity' => (int) $line['quantity'],
        ])->values()->all();

        $lineItems[] = PayMongoService::transactionFeeLineItem();

        return $this->startCheckout(
            $rental,
            $lineItems,
            "Equipment rental #{$rental->id} — Barangay San Jose",
            route('rentals.pay.callback', $rental),
            route('rentals.pay.cancel', $rental),
        );
    }

    public function paymentCallback(Request $request, EquipmentRental $rental)
    {
        abort_unless($rental->user_id === $request->user()->id, 403);

        $this->confirmPayment($rental, 'equipment_rentals');

        if ($rental->payment_status === 'paid') {
            return redirect()->route('rentals.receipt', $rental)
                ->with('success', 'Payment confirmed. Here is your receipt and claim code.');
        }

        return redirect()->route('rentals.index')
            ->with('error', 'We could not confirm your payment yet. If you completed it, check back shortly — otherwise you can try paying again.');
    }

    public function paymentCancelled(Request $request, EquipmentRental $rental)
    {
        abort_unless($rental->user_id === $request->user()->id, 403);

        return redirect()->route('rentals.index')
            ->with('error', 'Payment was not completed. You can try paying again from My Rentals.');
    }

    public function retryPayment(Request $request, EquipmentRental $rental)
    {
        abort_unless($rental->user_id === $request->user()->id, 403);
        abort_if($rental->payment_status === 'paid', 400);
        abort_if($rental->payment_method === 'cash', 400);

        $rental->load('items.equipment');

        $lineItems = $rental->items->map(fn ($line) => [
            'name'     => $line->equipment->name,
            'amount'   => PayMongoService::toCentavos($line->equipment->fee ?? 0),
            'currency' => 'PHP',
            'quantity' => $line->quantity,
        ])->all();

        $lineItems[] = PayMongoService::transactionFeeLineItem();

        return $this->startCheckout(
            $rental,
            $lineItems,
            "Equipment rental #{$rental->id} — Barangay San Jose",
            route('rentals.pay.callback', $rental),
            route('rentals.pay.cancel', $rental),
        );
    }

    /**
     * Resident asks to cancel a paid rental (or return it early) and get a
     * refund. This only records the request — an admin reviews it, sets the
     * final amount and processes the payout from the Refunds queue.
     */
    public function requestRefund(Request $request, EquipmentRental $rental)
    {
        abort_unless($rental->user_id === $request->user()->id, 403);

        $rental->load('items.equipment', 'refundRequests');

        if (! $rental->isRefundEligible()) {
            return back()->with('error', 'This rental is not eligible for a refund request, or one is already in progress.');
        }

        $estimate = RentalRefund::estimate($rental);

        if ($estimate['refundable'] <= 0) {
            return back()->with('error', 'The rental period is already complete, so no refund applies.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $refund = $rental->refundRequests()->create([
            'user_id'          => $request->user()->id,
            'reason'           => $validated['reason'],
            'type'             => $estimate['type'],
            'status'           => 'requested',
            'estimated_amount' => $estimate['refundable'],
        ]);

        $refund->load('rental.items.equipment', 'user');

        Notify::send($request->user(), new RefundRequestStatusNotification($refund, 'submitted'));

        foreach (User::where('role', 'admin')->get() as $admin) {
            Notify::send($admin, new RefundRequestStatusNotification($refund, 'admin_new'));
        }

        return back()->with('success', 'Your cancellation / refund request has been submitted. The barangay will review it shortly.');
    }

    public function receipt(Request $request, EquipmentRental $rental)
    {
        abort_unless($rental->user_id === $request->user()->id, 403);
        abort_unless($rental->claim_code, 404);

        $rental->load('items.equipment');

        $lines = $rental->items->map(fn ($line) => [
            'label' => "{$line->quantity}× {$line->equipment->name}",
            'value' => '₱' . number_format(($line->equipment->fee ?? 0) * $line->quantity, 2),
        ])->all();

        if ($rental->payment_method !== 'cash') {
            $lines[] = ['label' => 'Transaction Fee', 'value' => '₱' . number_format(PayMongoService::transactionFee(), 2)];
        }

        $receipt = [
            'title'            => 'Equipment Rental Receipt',
            'claimCode'        => $rental->claim_code,
            'residentName'     => $request->user()->name,
            'date'             => $rental->created_at,
            'lines'            => $lines,
            'amount'           => $rental->amount_due,
            'paymentMethod'    => $rental->payment_method,
            'paymentChannel'   => $rental->payment_channel,
            'paymentReference' => $rental->payment_reference,
            'note'             => 'Present this receipt and claim code to the barangay hall once your rental is approved.',
            'backRoute'        => route('rentals.index'),
        ];

        return view('receipts.show', compact('receipt'));
    }

    private function startCheckout(
        EquipmentRental $record,
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
                "rental-{$record->id}",
            );
        } catch (\Throwable $e) {
            Log::error('PayMongo checkout session creation failed', ['error' => $e->getMessage(), 'rental_id' => $record->id]);

            return redirect()->route('rentals.index')
                ->with('error', 'We could not start the online payment right now. Please try again in a moment, or choose Cash instead.');
        }

        $record->update(['paymongo_checkout_session_id' => $session['id']]);

        return redirect()->away($session['checkout_url']);
    }

    private function confirmPayment(EquipmentRental $rental, string $table): void
    {
        if ($rental->payment_status === 'paid' || ! $rental->paymongo_checkout_session_id) {
            return;
        }

        try {
            $session = $this->payMongo->retrieveCheckoutSession($rental->paymongo_checkout_session_id);
        } catch (\Throwable $e) {
            Log::error('PayMongo checkout session lookup failed', ['error' => $e->getMessage(), 'rental_id' => $rental->id]);

            return;
        }

        if ($this->payMongo->isPaid($session)) {
            $rental->update([
                'payment_status'    => 'paid',
                'payment_channel'   => $this->payMongo->paidChannel($session),
                'payment_reference' => $this->payMongo->paidReference($session),
                'claim_code'        => ClaimCode::next($table),
            ]);

            $rental->deductStock();

            $rental->load('user', 'items.equipment');
            Notify::send($rental->user, new EquipmentRentalStatusNotification($rental, 'payment_confirmed'));
        }
    }

    private function ensureActive(Request $request): void
    {
        if (! $request->user()->isActive()) {
            abort(403, 'Your account is still pending review by the barangay. You cannot rent equipment yet.');
        }
    }
}
