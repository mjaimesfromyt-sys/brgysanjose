<?php

namespace App\Http\Controllers;
use App\Events\NewTransactionEvent;

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

        
        event(new NewTransactionEvent('Document Request', 'New Document Request', auth()->user()->name ?? 'Resident', 'DOC-' . ($requestRecord->id ?? rand(100, 999)), route('admin.requests.index')));
        return view('requests.index', compact('requests'));
    }

    public function create(Request $request)
    {
        $this->ensureActive($request);

        $types = TransactionType::with('requirements')
            ->orderBy('name')
            ->get();

        // Field mapping: slug => list sa field groups nga kinahanglan
        $fieldMap = [];
        foreach (config('certificate_fields', []) as $group => $slugs) {
            foreach ($slugs as $slug) {
                $fieldMap[$slug][] = $group;
            }
        }

        return view('requests.create', compact('types', 'fieldMap'));
    }

    public function store(Request $request)
    {
        $this->ensureActive($request);

        $validated = $request->validate([
            'transaction_type_id' => ['required', 'exists:transaction_types,id'],
            'purpose'             => ['nullable', 'string', 'max:255'],
            'business_name'       => ['nullable', 'string', 'max:255'],
            'business_nature'     => ['nullable', 'string', 'max:255'],
            'business_address'    => ['nullable', 'string', 'max:255'],
            'occupation'          => ['nullable', 'string', 'max:255'],
            'monthly_income'      => ['nullable', 'numeric', 'min:0'],
            'employed_since'      => ['nullable', 'string', 'max:100'],
            // Certificate-specific fields
            'subject_name'        => ['nullable', 'string', 'max:255'],
            'requester_name'      => ['nullable', 'string', 'max:255'],
            'lot_no'              => ['nullable', 'string', 'max:100'],
            'tax_dec_no'          => ['nullable', 'string', 'max:100'],
            'cad_no'              => ['nullable', 'string', 'max:100'],
            'land_area'           => ['nullable', 'string', 'max:255'],
            'land_owner'          => ['nullable', 'string', 'max:255'],
            'owner_spouse'        => ['nullable', 'string', 'max:255'],
            'date_died'           => ['nullable', 'date'],
            'burial_place'        => ['nullable', 'string', 'max:255'],
            'control_no'          => ['nullable', 'string', 'max:100'],
            'disability_type'     => ['nullable', 'string', 'max:255'],
            'solo_parent_since'   => ['nullable', 'string', 'max:100'],
            'father_name'         => ['nullable', 'string', 'max:255'],
            'mother_name'         => ['nullable', 'string', 'max:255'],
            'employer_name'       => ['nullable', 'string', 'max:255'],
            'activity_date'       => ['nullable', 'date'],
            'name_list'           => ['nullable', 'string', 'max:2000'],
            'payment_method'      => ['required', 'in:cash,gcash,paymaya,bank_transfer'],
        ]);

        $type = TransactionType::findOrFail($validated['transaction_type_id']);

        if ($type->requires_residency && ! $request->user()->isResident()) {
            throw ValidationException::withMessages([
                'transaction_type_id' => 'This document is only available to verified residents of Barangay San Jose.',
            ]);
        }

        // 👉 I-SAVE ANG BUSINESS NAME UG NATURE OF BUSINESS
        $purpose = $validated['purpose'] ?? null;
        if (!empty($validated['business_name'])) {
            $bName = trim($validated['business_name']);
            $bNature = trim($validated['business_nature'] ?? 'Advertising');
            $purpose = $bName . ' (' . $bNature . ')' . ($purpose ? ' - ' . $purpose : '');
        }

        $amountDue = (float) ($type->fee ?? 0);
        $isCashless = $validated['payment_method'] !== 'cash';

        if ($isCashless && $amountDue > 0) {
            $amountDue += PayMongoService::transactionFee();
        }

        $docRequest = $request->user()->documentRequests()->create([
            'transaction_type_id' => $type->id,
            'purpose'             => $purpose,
            'business_address'    => $validated['business_address'] ?? null,
            'occupation'          => $validated['occupation'] ?? null,
            'monthly_income'      => $validated['monthly_income'] ?? null,
            'employed_since'      => $validated['employed_since'] ?? null,
            // Certificate-specific fields
            'subject_name'        => $validated['subject_name'] ?? null,
            'requester_name'      => $validated['requester_name'] ?? null,
            'lot_no'              => $validated['lot_no'] ?? null,
            'tax_dec_no'          => $validated['tax_dec_no'] ?? null,
            'cad_no'              => $validated['cad_no'] ?? null,
            'land_area'           => $validated['land_area'] ?? null,
            'land_owner'          => $validated['land_owner'] ?? ($validated['father_name'] ?? $validated['employer_name'] ?? null),
            'owner_spouse'        => $validated['owner_spouse'] ?? ($validated['mother_name'] ?? null),
            'date_died'           => $validated['date_died'] ?? ($validated['activity_date'] ?? null),
            'burial_place'        => $validated['burial_place'] ?? null,
            'control_no'          => $validated['control_no'] ?? null,
            'disability_type'     => $validated['disability_type'] ?? null,
            'solo_parent_since'   => $validated['solo_parent_since'] ?? null,
            'deceased_name'       => $validated['name_list'] ?? null,
            'tenant_name'         => $validated['name_list'] ?? null,
            'status'              => 'pending',
            'payment_method'      => $validated['payment_method'],
            'amount_due'          => $amountDue,
            'payment_status'      => $amountDue > 0 ? 'unpaid' : 'paid',
        ]);

        if (! $isCashless || $amountDue <= 0) {
            return redirect()->route('requests.index')
                ->with('success', 'Document request submitted. It is now pending verification.');
        }

        return $this->startCheckout($docRequest, $type, $validated['payment_method']);
    }

    private function startCheckout(DocumentRequest $docRequest, TransactionType $type, string $method)
    {
        try {
            $lineItems = [
                [
                    'name'     => $type->name,
                    'amount'   => PayMongoService::toCentavos($type->fee ?? 0),
                    'currency' => 'PHP',
                    'quantity' => 1,
                ],
                PayMongoService::transactionFeeLineItem(),
            ];

            $checkout = $this->payMongo->createCheckoutSession([
                'line_items'           => $lineItems,
                'payment_method_types' => ["qrph", "gcash", "paymaya"],
                'success_url'          => route('requests.pay.callback', ['documentRequest' => $docRequest->id]) . '?status=success',
                'cancel_url'           => route('requests.pay.cancel', ['documentRequest' => $docRequest->id]),
                'description'          => 'Document Request: ' . $type->name,
            ]);

            $docRequest->update([
                'payment_reference' => $checkout['id'] ?? null,
            ]);

            return redirect()->away($checkout['checkout_url']);
        } catch (\Throwable $e) {
            Log::error('PayMongo Checkout Error: ' . $e->getMessage());
            return redirect()->route('requests.index')
                ->with('warning', 'Request submitted, but online payment session could not be created. You may pay via cash at the hall.');
        }
    }

    public function paymentCallback(Request $request, DocumentRequest $documentRequest)
    {
        abort_unless($documentRequest->user_id === $request->user()->id, 403);

        // PayMongo redirects the payer here. Never trust the redirect alone —
        // ask PayMongo's API whether the checkout session was actually paid.
        $this->verifyAndConfirm($documentRequest);

        if ($documentRequest->fresh()->payment_status === 'paid') {
            return redirect()->route('requests.index')
                ->with('success', 'Payment successful! Your request is pending verification.');
        }

        return redirect()->route('requests.index')
            ->with('warning', 'We could not confirm your payment yet. If you already paid, the confirmation usually arrives within a few minutes.');
    }

    /**
     * Re-check the checkout session with PayMongo, then confirm through the
     * shared PaymentConfirmer (same idempotent path the webhook uses).
     */
    private function verifyAndConfirm(DocumentRequest $documentRequest): void
    {
        if ($documentRequest->payment_status === 'paid') {
            return; // Webhook may have beaten us here.
        }

        $sessionId = $documentRequest->paymongo_checkout_session_id
            ?: (str_starts_with((string) $documentRequest->payment_reference, 'cs_') ? $documentRequest->payment_reference : null);

        if (! $sessionId) {
            return;
        }

        try {
            $session = $this->payMongo->retrieveCheckoutSession($sessionId);

            if ($this->payMongo->isPaid($session)) {
                app(\App\Services\PaymentConfirmer::class)->confirm(
                    $documentRequest,
                    (string) ($this->payMongo->paidChannel($session) ?? 'qrph'),
                    (string) ($this->payMongo->paidReference($session) ?? ''),
                );
            }
        } catch (\Throwable $e) {
            Log::error('PayMongo callback verification failed', [
                'document_request_id' => $documentRequest->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function paymentCancelled(DocumentRequest $documentRequest)
    {
        return redirect()->route('requests.index')
            ->with('info', 'Payment was cancelled. You can pay at the barangay hall or retry online.');
    }

    public function receipt(Request $request, DocumentRequest $documentRequest)
    {
        abort_unless($documentRequest->user_id === $request->user()->id, 403);

        $receipt = [
            'title'            => $documentRequest->transactionType->name . ' Receipt',
            'claimCode'        => $documentRequest->claim_code,
            'residentName'     => $request->user()->name,
            'date'             => $documentRequest->created_at,
            'lines'            => [
                [
                    'label' => $documentRequest->transactionType->name,
                    'value' => '₱' . number_format($documentRequest->transactionType->fee ?? 0, 2),
                ],
            ],
            'amount'           => $documentRequest->amount_due ?? ($documentRequest->transactionType->fee ?? 0),
            'paymentMethod'    => $documentRequest->payment_method,
            'paymentChannel'   => null,
            'paymentReference' => $documentRequest->payment_reference,
            'note'             => 'Present this receipt and claim code to the barangay hall to claim your document.',
            'backRoute'        => route('requests.index'),
        ];

        return view('receipts.show', compact('receipt'));
    }

    public function retryPayment(DocumentRequest $documentRequest)
    {
        if ($documentRequest->payment_status === 'paid') {
            return redirect()->route('requests.index')->with('info', 'This document request has already been paid.');
        }

        $type = $documentRequest->transactionType;
        return $this->startCheckout($documentRequest, $type, $documentRequest->payment_method ?? 'gcash');
    }

    private function ensureActive(Request $request): void
    {
        if (! $request->user()->isActive()) {
            abort(403, 'Your account is pending verification.');
        }
    }
}
