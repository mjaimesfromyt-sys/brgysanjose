<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefundRequest;
use App\Notifications\RefundRequestStatusNotification;
use App\Services\PayMongoService;
use App\Support\Notify;
use App\Support\RentalRefund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RefundRequestController extends Controller
{
    public function __construct(private readonly PayMongoService $payMongo)
    {
    }

    public function index(Request $request)
    {
        $status = $request->query('status', 'requested');

        $tabs = [
            'requested' => 'To review',
            'approved'  => 'To pay out',
            'refunded'  => 'Refunded',
            'rejected'  => 'Rejected',
        ];

        $refunds = RefundRequest::with(['rental.items.equipment', 'user', 'reviewer'])
            ->when(array_key_exists($status, $tabs), fn ($q) => $q->where('status', $status))
            ->latest()
            ->get();

        $counts = collect(array_keys($tabs))
            ->mapWithKeys(fn ($key) => [$key => RefundRequest::where('status', $key)->count()])
            ->all();

        return view('admin.refunds.index', compact('refunds', 'status', 'tabs', 'counts'));
    }

    public function approve(Request $request, RefundRequest $refundRequest)
    {
        if ($refundRequest->status !== 'requested') {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $rental   = $refundRequest->rental;
        $estimate = RentalRefund::estimate($rental);

        $validated = $request->validate([
            'amount'        => ['required', 'numeric', 'min:0', 'max:' . $estimate['refundable']],
            'admin_remarks' => ['nullable', 'string', 'max:500'],
        ], [
            'amount.max' => 'The refund cannot exceed the refundable rental fee (₱' . number_format($estimate['refundable'], 2) . ').',
        ]);

        $refundRequest->update([
            'status'        => 'approved',
            'amount'        => $validated['amount'],
            'reviewed_by'   => $request->user()->id,
            'admin_remarks' => $validated['admin_remarks'] ?? null,
        ]);

        // Close out the rental itself and put the stock back.
        if ($rental->status === 'released') {
            $rental->update(['status' => 'returned', 'returned_at' => now()]);
        } else {
            $rental->update(['status' => 'cancelled']);
        }
        if ($rental->payment_status === 'paid') {
            $rental->restoreStock();
        }

        $refundRequest->load('rental.items.equipment', 'user');
        Notify::send($refundRequest->user, new RefundRequestStatusNotification($refundRequest, 'approved'));

        return back()->with('success', 'Refund approved. Now process the payout from the "To pay out" tab.');
    }

    public function reject(Request $request, RefundRequest $refundRequest)
    {
        if ($refundRequest->status !== 'requested') {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $validated = $request->validate([
            'admin_remarks' => ['required', 'string', 'max:500'],
        ], [
            'admin_remarks.required' => 'Please give the resident a reason for the rejection.',
        ]);

        $refundRequest->update([
            'status'        => 'rejected',
            'reviewed_by'   => $request->user()->id,
            'admin_remarks' => $validated['admin_remarks'],
        ]);

        $refundRequest->load('rental.items.equipment', 'user');
        Notify::send($refundRequest->user, new RefundRequestStatusNotification($refundRequest, 'rejected'));

        return back()->with('success', 'Refund request rejected and the resident has been notified.');
    }

    /**
     * Pay the refund out. Cash refunds just record a reference. Online
     * refunds call the PayMongo Refunds API, with a manual reference as the
     * fallback if the API call fails or there is no payment on record.
     */
    public function process(Request $request, RefundRequest $refundRequest)
    {
        if ($refundRequest->status !== 'approved') {
            return back()->with('error', 'Only approved requests can be processed.');
        }

        $validated = $request->validate([
            'manual_reference' => ['nullable', 'string', 'max:190'],
        ]);

        $rental   = $refundRequest->rental;
        $isCash   = $rental->payment_method === 'cash';
        $manual   = trim((string) ($validated['manual_reference'] ?? ''));
        $amount   = (float) $refundRequest->amount;

        // Nothing to move.
        if ($amount <= 0) {
            $refundRequest->update([
                'status'           => 'refunded',
                'refund_method'    => $isCash ? 'cash' : 'online',
                'refund_reference' => 'No refund due (₱0.00)',
                'processed_at'     => now(),
            ]);
            $this->notifyRefunded($refundRequest);

            return back()->with('success', 'Marked as refunded (₱0.00 — nothing to pay out).');
        }

        // Cash, or an admin who chose to record the payout manually.
        if ($isCash || $manual !== '') {
            if ($manual === '') {
                throw ValidationException::withMessages([
                    'manual_reference' => 'Enter a reference for the cash refund (e.g. OR number or "handed to resident").',
                ]);
            }

            $refundRequest->update([
                'status'           => 'refunded',
                'refund_method'    => $isCash ? 'cash' : 'online',
                'refund_reference' => $manual,
                'processed_at'     => now(),
            ]);
            $this->notifyRefunded($refundRequest);

            return back()->with('success', 'Refund recorded and the resident has been notified.');
        }

        // Online, automatic via PayMongo.
        if (! $rental->payment_reference) {
            return back()->with('error', 'No PayMongo payment is on record for this rental. Refund it manually and enter the reference.');
        }

        try {
            $result = $this->payMongo->refundPayment(
                $rental->payment_reference,
                $amount,
                'requested_by_customer',
            );
        } catch (\Throwable $e) {
            Log::error('PayMongo refund failed', [
                'refund_request_id' => $refundRequest->id,
                'rental_id'         => $rental->id,
                'error'             => $e->getMessage(),
            ]);

            return back()->with('error', 'PayMongo refused the refund: ' . $e->getMessage()
                . ' You can retry, or refund manually and enter the reference below.');
        }

        $refundRequest->update([
            'status'             => 'refunded',
            'refund_method'      => 'online',
            'paymongo_refund_id' => $result['id'],
            'refund_reference'   => $result['id'],
            'processed_at'       => now(),
        ]);
        $this->notifyRefunded($refundRequest);

        return back()->with('success', 'Refund of ₱' . number_format($amount, 2) . ' sent via PayMongo (' . $result['id'] . ').');
    }

    private function notifyRefunded(RefundRequest $refundRequest): void
    {
        $refundRequest->load('rental.items.equipment', 'user');
        Notify::send($refundRequest->user, new RefundRequestStatusNotification($refundRequest, 'refunded'));
    }
}
