<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
use App\Models\TransactionType;
use App\Notifications\DocumentRequestStatusNotification;
use App\Services\PayMongoService;
use App\Support\ClaimCode;
use App\Support\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DocumentRequestController extends Controller
{
    public function __construct(private readonly PayMongoService $payMongo)
    {
    }

    public function index(Request $request)
    {
        $requests = $request->user()->documentRequests()
            ->with('transactionType')
            ->latest()
            ->get();

        return view('requests.index', compact('requests'));
    }

    public function create(Request $request)
    {
        $this->ensureActive($request);

        $types = TransactionType::where('is_active', true)
            ->with('requirements')
            ->orderBy('name')
            ->get();

        return view('requests.create', compact('types'));
    }

    public function store(Request $request)
    {
        $this->ensureActive($request);

        $validated = $request->validate([
            'transaction_type_id' => ['required', 'exists:transaction_types,id'],
            'purpose'             => ['nullable', 'string', 'max:255'],
            'payment_method'      => ['required', 'in:cash,gcash,paymaya,bank_transfer'],
        ]);

        $type = TransactionType::findOrFail($validated['transaction_type_id']);

        // Enforce the residency rule
        if ($type->requires_residency && ! $request->user()->isResident()) {
            throw ValidationException::withMessages([
                'transaction_type_id' => 'This transaction is available to verified barangay residents only. Your account is not registered as a resident.',
            ]);
        }

        if (! $type->is_active) {
            throw ValidationException::withMessages([
                'transaction_type_id' => 'This transaction is not currently available.',
            ]);
        }

        $amountDue = $type->fee ?? 0;
        $isCashless = $validated['payment_method'] !== 'cash';

        if ($isCashless) {
            $amountDue += PayMongoService::transactionFee();
        }

        $documentRequest = $request->user()->documentRequests()->create([
            'transaction_type_id' => $type->id,
            'purpose'             => $validated['purpose'] ?? null,
            'status'              => 'pending',
            'payment_method'      => $validated['payment_method'],
            'amount_due'          => $amountDue,
            'payment_status'      => 'unpaid',
        ]);

        if (! $isCashless) {
            return redirect()->route('requests.index')
                ->with('success', 'Request submitted. It is now pending validation by the barangay.');
        }

        return $this->startCheckout(
            $documentRequest,
            [
                [
                    'name'     => $type->name,
                    'amount'   => PayMongoService::toCentavos($type->fee ?? 0),
                    'currency' => 'PHP',
                    'quantity' => 1,
                ],
                PayMongoService::transactionFeeLineItem(),
            ],
            "Document request #{$documentRequest->id} — Barangay San Jose",
            route('requests.pay.callback', $documentRequest),
            route('requests.pay.cancel', $documentRequest),
        );
    }

    public function paymentCallback(Request $request, DocumentRequest $documentRequest)
    {
        abort_unless($documentRequest->user_id === $request->user()->id, 403);

        $this->confirmPayment($documentRequest);

        if ($documentRequest->payment_status === 'paid') {
            return redirect()->route('requests.receipt', $documentRequest)
                ->with('success', 'Payment confirmed. Here is your receipt and claim code.');
        }

        return redirect()->route('requests.index')
            ->with('error', 'We could not confirm your payment yet. If you completed it, check back shortly — otherwise you can try paying again.');
    }

    public function paymentCancelled(Request $request, DocumentRequest $documentRequest)
    {
        abort_unless($documentRequest->user_id === $request->user()->id, 403);

        return redirect()->route('requests.index')
            ->with('error', 'Payment was not completed. You can try paying again from My Requests.');
    }

    public function retryPayment(Request $request, DocumentRequest $documentRequest)
    {
        abort_unless($documentRequest->user_id === $request->user()->id, 403);
        abort_if($documentRequest->payment_status === 'paid', 400);
        abort_if($documentRequest->payment_method === 'cash', 400);

        $documentRequest->load('transactionType');

        return $this->startCheckout(
            $documentRequest,
            [
                [
                    'name'     => $documentRequest->transactionType->name,
                    'amount'   => PayMongoService::toCentavos($documentRequest->transactionType->fee ?? 0),
                    'currency' => 'PHP',
                    'quantity' => 1,
                ],
                PayMongoService::transactionFeeLineItem(),
            ],
            "Document request #{$documentRequest->id} — Barangay San Jose",
            route('requests.pay.callback', $documentRequest),
            route('requests.pay.cancel', $documentRequest),
        );
    }

    public function receipt(Request $request, DocumentRequest $documentRequest)
    {
        abort_unless($documentRequest->user_id === $request->user()->id, 403);
        abort_unless($documentRequest->claim_code, 404);

        $documentRequest->load('transactionType');

        $lines = [
            ['label' => $documentRequest->transactionType->name, 'value' => '₱' . number_format($documentRequest->transactionType->fee ?? 0, 2)],
        ];

        if ($documentRequest->payment_method !== 'cash') {
            $lines[] = ['label' => 'Transaction Fee', 'value' => '₱' . number_format(PayMongoService::transactionFee(), 2)];
        }

        $receipt = [
            'title'            => 'Document Request Receipt',
            'claimCode'        => $documentRequest->claim_code,
            'residentName'     => $request->user()->name,
            'date'             => $documentRequest->created_at,
            'lines'            => $lines,
            'amount'           => $documentRequest->amount_due,
            'paymentMethod'    => $documentRequest->payment_method,
            'paymentChannel'   => $documentRequest->payment_channel,
            'paymentReference' => $documentRequest->payment_reference,
            'note'             => 'Present this receipt and claim code at the barangay hall to claim your document.',
            'backRoute'        => route('requests.index'),
        ];

        return view('receipts.show', compact('receipt'));
    }

    private function startCheckout(
        DocumentRequest $record,
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
                "request-{$record->id}",
            );
        } catch (\Throwable $e) {
            Log::error('PayMongo checkout session creation failed', ['error' => $e->getMessage(), 'document_request_id' => $record->id]);

            return redirect()->route('requests.index')
                ->with('error', 'We could not start the online payment right now. Please try again in a moment, or choose Cash instead.');
        }

        $record->update(['paymongo_checkout_session_id' => $session['id']]);

        return redirect()->away($session['checkout_url']);
    }

    private function confirmPayment(DocumentRequest $documentRequest): void
    {
        if ($documentRequest->payment_status === 'paid' || ! $documentRequest->paymongo_checkout_session_id) {
            return;
        }

        try {
            $session = $this->payMongo->retrieveCheckoutSession($documentRequest->paymongo_checkout_session_id);
        } catch (\Throwable $e) {
            Log::error('PayMongo checkout session lookup failed', ['error' => $e->getMessage(), 'document_request_id' => $documentRequest->id]);

            return;
        }

        if ($this->payMongo->isPaid($session)) {
            $documentRequest->update([
                'payment_status'    => 'paid',
                'payment_channel'   => $this->payMongo->paidChannel($session),
                'payment_reference' => $this->payMongo->paidReference($session),
                'claim_code'        => ClaimCode::next('document_requests'),
            ]);

            $documentRequest->load('user', 'transactionType');
            Notify::send($documentRequest->user, new DocumentRequestStatusNotification($documentRequest, 'payment_confirmed'));
        }
    }

    private function ensureActive(Request $request): void
    {
        if (! $request->user()->isActive()) {
            abort(403, 'Your account is still pending review by the barangay. You cannot submit requests yet.');
        }
    }
}
