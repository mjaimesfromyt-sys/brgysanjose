<?php

namespace App\Http\Controllers\Admin;
use App\Events\ResidentStatusUpdatedEvent;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequest;
use App\Notifications\DocumentRequestStatusNotification;
use App\Support\ClaimCode;
use App\Support\Notify;
use Illuminate\Http\Request;

class DocumentRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $requests = DocumentRequest::with(['user', 'transactionType'])
            ->when(
                in_array($status, ['pending', 'validated', 'claimed', 'rejected']),
                fn ($q) => $q->where('status', $status)
            )
            ->latest()
            ->get();

        $counts = [
            'pending'   => DocumentRequest::where('status', 'pending')->count(),
            'validated' => DocumentRequest::where('status', 'validated')->count(),
            'claimed'   => DocumentRequest::where('status', 'claimed')->count(),
            'rejected'  => DocumentRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.requests.index', compact('requests', 'status', 'counts'));
    }

    public function validateRequest(Request $request, DocumentRequest $documentRequest)
    {
        if ($documentRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        $oldStatus = $documentRequest->status;

        $documentRequest->update([
            'status'       => 'validated',
            'claim_code'   => $documentRequest->claim_code ?? ClaimCode::next('document_requests'),
            'verification_code' => $documentRequest->verification_code
                ?: 'v1_' . bin2hex(random_bytes(20)),
            'control_number' => $documentRequest->control_number
                ?: 'BC-' . date('Ymd') . '-' . sprintf('%04d', $documentRequest->id),
            'reviewed_by'  => $request->user()->id,
            'validated_at' => now(),
        ]);

        activity('document_requests')
            ->causedBy($request->user())
            ->performedOn($documentRequest)
            ->withProperties([
                'action' => 'validated',
                'old_status' => $oldStatus,
                'new_status' => 'validated',
            ])
            ->log('Document request validated');

        $documentRequest->load('user', 'transactionType');

        Notify::send(
            $documentRequest->user,
            new DocumentRequestStatusNotification($documentRequest, 'validated')
        );

        return back()->with(
            'success',
            "Request validated. Claim code: {$documentRequest->claim_code}"
        );
    }

    public function reject(Request $request, DocumentRequest $documentRequest)
    {
        $validated = $request->validate([
            'admin_remarks' => ['nullable', 'string', 'max:500'],
        ]);

        if ($documentRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        $oldStatus = $documentRequest->status;

        $documentRequest->update([
            'status'        => 'rejected',
            'admin_remarks' => $validated['admin_remarks'] ?? null,
            'reviewed_by'   => $request->user()->id,
        ]);

        activity('document_requests')
            ->causedBy($request->user())
            ->performedOn($documentRequest)
            ->withProperties([
                'action' => 'rejected',
                'old_status' => $oldStatus,
                'new_status' => 'rejected',
            ])
            ->log('Document request rejected');

        $documentRequest->load('user', 'transactionType');

        Notify::send(
            $documentRequest->user,
            new DocumentRequestStatusNotification($documentRequest, 'rejected')
        );

        return back()->with('success', 'Request rejected.');
    }

    public function markClaimed(Request $request, DocumentRequest $documentRequest)
    {
        if ($documentRequest->status !== 'validated') {
            return back()->with(
                'error',
                'Only validated requests can be marked as claimed.'
            );
        }

        $oldStatus = $documentRequest->status;

        $documentRequest->update([
            'status'     => 'claimed',
            'claimed_at' => now(),
        ]);

        activity('document_requests')
            ->causedBy($request->user())
            ->performedOn($documentRequest)
            ->withProperties([
                'action' => 'claimed',
                'old_status' => $oldStatus,
                'new_status' => 'claimed',
            ])
            ->log('Document request marked as claimed');

        return back()->with('success', 'Request marked as claimed.');
    }

    public function markPaid(Request $request, DocumentRequest $documentRequest)
    {
        if (
            $documentRequest->payment_method !== 'cash' ||
            $documentRequest->payment_status !== 'unpaid'
        ) {
            return back()->with(
                'error',
                'Only unpaid cash requests can be marked paid.'
            );
        }

        $oldPaymentStatus = $documentRequest->payment_status;

        $documentRequest->update([
            'payment_status' => 'paid',
        ]);

        activity('document_requests')
            ->causedBy($request->user())
            ->performedOn($documentRequest)
            ->withProperties([
                'action' => 'payment_marked_paid',
                'payment_method' => 'cash',
                'old_payment_status' => $oldPaymentStatus,
                'new_payment_status' => 'paid',
            ])
            ->log('Document request cash payment marked as paid');

        $documentRequest->load('user', 'transactionType');

        Notify::send(
            $documentRequest->user,
            new DocumentRequestStatusNotification(
                $documentRequest,
                'payment_confirmed'
            )
        );

        return back()->with('success', 'Request marked as paid.');
    }
}