<?php

namespace App\Http\Controllers;
use App\Events\NewTransactionEvent;

use App\Models\Equipment;
use App\Models\EquipmentRental;
use App\Services\PayMongoService;
use Carbon\Carbon;
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
            ->with('items.equipment')
            ->latest('start_date')
            ->get();

        
        event(new NewTransactionEvent('Equipment Rental', 'New Equipment Rental', auth()->user()->name ?? 'Resident', 'EQ-' . ($rental->id ?? rand(100, 999)), route('admin.rentals.index')));
        return view('rentals.index', compact('rentals'));
    }

    public function create(Request $request)
    {
        $this->ensureActive($request);

        $equipment = Equipment::where('is_active', true)->orderBy('name')->get();

        // Compute ang aktwal nga availability base sa gipiling petsa
        $startDate = $request->input('start_date', now()->toDateString());
        $endDate   = $request->input('end_date', $startDate);

        foreach ($equipment as $item) {
            $item->available_stock = $item->availableFor($startDate, $endDate);
        }

        return view('rentals.create', compact('equipment', 'startDate', 'endDate'));
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
            'payment_method'       => ['required', 'in:cash,gcash,cashless,paymaya,bank_transfer'],
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

        // Gidaghanon sa Adlaw
        $startDate = Carbon::parse($validated['start_date']);
        $endDate   = Carbon::parse($validated['end_date']);
        $daysCount = $startDate->diffInDays($endDate) + 1; // e.g. Sep 4 to Sep 5 = 2 days

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

            // 👉 CEMENT MIXER PER DAY LOGIC
            $isPerDay = str_contains(strtolower($item->name), 'mixer');
            $multiplier = $isPerDay ? $daysCount : 1;

            $amountDue += ($item->fee ?? 0) * (int) $line['quantity'] * $multiplier;
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
                ->with('success', 'Equipment rental request submitted. Total amount due: ₱' . number_format($amountDue, 2) . ' (' . $daysCount . ' day/s). Pending admin approval.');
        }

        $lineItems = $lines->map(function ($line) use ($equipmentById, $daysCount) {
            $item = $equipmentById[$line['equipment_id']];
            $isPerDay = str_contains(strtolower($item->name), 'mixer');
            $unitFee = (float) ($item->fee ?? 0);
            $finalUnitPrice = $isPerDay ? ($unitFee * $daysCount) : $unitFee;

            $label = $item->name . ($isPerDay ? " ({$daysCount} days @ ₱" . number_format($unitFee, 2) . "/day)" : '');

            return [
                'name'     => $label,
                'amount'   => PayMongoService::toCentavos($finalUnitPrice),
                'currency' => 'PHP',
                'quantity' => (int) $line['quantity'],
            ];
        })->values()->all();

        if ($isCashless) {
            $lineItems[] = PayMongoService::transactionFeeLineItem();
        }

        return $this->startCheckout($rental, $lineItems, $validated['payment_method']);
    }

    private function startCheckout(EquipmentRental $record, array $lineItems, string $method)
    {
        try {
            $checkout = $this->payMongo->createCheckoutSession([
                'line_items'           => $lineItems,
                'payment_method_types' => ['qrph', 'gcash', 'paymaya'],
                'success_url'          => route('rentals.pay.callback', ['rental' => $record->id]) . '?status=success',
                'cancel_url'           => route('rentals.pay.cancel', ['rental' => $record->id]),
                'description'          => 'Equipment Rental #' . $record->id,
            ]);

            $record->update([
                'payment_reference' => $checkout['id'] ?? null,
            ]);

            return redirect()->away($checkout['checkout_url']);
        } catch (\Throwable $e) {
            Log::error('PayMongo Checkout Error: ' . $e->getMessage());
            return redirect()->route('rentals.index')
                ->with('warning', 'Rental submitted, but cashless checkout could not be created. You can pay via cash at the hall.');
        }
    }

    public function paymentCallback(Request $request, EquipmentRental $rental)
    {
        abort_unless($rental->user_id === $request->user()->id, 403);

        // PayMongo redirects the payer here. Never trust the redirect alone —
        // ask PayMongo's API whether the checkout session was actually paid.
        $this->verifyAndConfirm($rental);

        if ($rental->fresh()->payment_status === 'paid') {
            return redirect()->route('rentals.index')
                ->with('success', 'Payment successful! Your rental request is pending approval.');
        }

        return redirect()->route('rentals.index')
            ->with('warning', 'We could not confirm your payment yet. If you already paid, the confirmation usually arrives within a few minutes.');
    }

    /**
     * Re-check the checkout session with PayMongo, then confirm through the
     * shared PaymentConfirmer (same idempotent path the webhook uses).
     */
    private function verifyAndConfirm(EquipmentRental $rental): void
    {
        if ($rental->payment_status === 'paid') {
            return; // Webhook may have beaten us here.
        }

        $sessionId = $rental->paymongo_checkout_session_id
            ?: (str_starts_with((string) $rental->payment_reference, 'cs_') ? $rental->payment_reference : null);

        if (! $sessionId) {
            return;
        }

        try {
            $session = $this->payMongo->retrieveCheckoutSession($sessionId);

            if ($this->payMongo->isPaid($session)) {
                app(\App\Services\PaymentConfirmer::class)->confirm(
                    $rental,
                    (string) ($this->payMongo->paidChannel($session) ?? 'qrph'),
                    (string) ($this->payMongo->paidReference($session) ?? ''),
                );
            }
        } catch (\Throwable $e) {
            Log::error('PayMongo callback verification failed', [
                'rental_id' => $rental->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function paymentCancelled(EquipmentRental $rental)
    {
        return redirect()->route('rentals.index')
            ->with('info', 'Payment was cancelled. You can retry anytime.');
    }

    public function receipt(Request $request, EquipmentRental $rental)
    {
        abort_unless($rental->user_id === $request->user()->id, 403);

        $receipt = [
            'title'            => 'Equipment Rental Receipt',
            'claimCode'        => $rental->claim_code,
            'residentName'     => $request->user()->name,
            'date'             => $rental->created_at,
            'lines'            => $rental->items->map(fn ($item) => [
                'label' => $item->quantity . '× ' . $item->equipment->name,
                'value' => '₱' . number_format($item->equipment->fee * $item->quantity, 2),
            ])->all(),
            'amount'           => $rental->amount_due ?? 0,
            'paymentMethod'    => $rental->payment_method,
            'paymentChannel'   => null,
            'paymentReference' => $rental->payment_reference,
            'note'             => 'Present this receipt and claim code to the barangay hall once your rental is approved.',
            'backRoute'        => route('rentals.index'),
        ];

        return view('receipts.show', compact('receipt'));
    }

    public function retryPayment(EquipmentRental $rental)
    {
        if ($rental->payment_status === 'paid') {
            return redirect()->route('rentals.index')->with('info', 'This rental has already been paid.');
        }

        $rentalFee = max($rental->amount_due - \App\Services\PayMongoService::transactionFee(), 0);

        $lineItems = [
            [
                'name'     => 'Equipment Rental #' . $rental->id,
                'amount'   => \App\Services\PayMongoService::toCentavos($rentalFee),
                'currency' => 'PHP',
                'quantity' => 1,
            ],
            \App\Services\PayMongoService::transactionFeeLineItem(),
        ];

        return $this->startCheckout($rental, $lineItems, $rental->payment_method ?? 'gcash');
    }

    private function ensureActive(Request $request): void
    {
        if (! $request->user()->isActive()) {
            abort(403, 'Your account is pending verification.');
        }
    }
}
