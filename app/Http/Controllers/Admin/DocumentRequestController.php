<?php

namespace App\Http\Controllers\Admin;

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
            ->when(in_array($status, ['pending', 'validated', 'claimed', 'rejected']),
                fn ($q) => $q->where('status', $status))
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

        $documentRequest->update([
            'status'       => 'validated',
            'claim_code'   => $documentRequest->claim_code ?? ClaimCode::next('document_requests'),
            'reviewed_by'  => $request->user()->id,
            'validated_at' => now(),
        ]);

        $documentRequest->load('user', 'transactionType');
        Notify::send($documentRequest->user, new DocumentRequestStatusNotification($documentRequest, 'validated'));

        return back()->with('success', "Request validated. Claim code: {$documentRequest->claim_code}");
    }

    public function reject(Request $request, DocumentRequest $documentRequest)
    {
        $validated = $request->validate([
            'admin_remarks' => ['nullable', 'string', 'max:500'],
        ]);

        if ($documentRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        $documentRequest->update([
            'status'        => 'rejected',
            'admin_remarks' => $validated['admin_remarks'] ?? null,
            'reviewed_by'   => $request->user()->id,
        ]);

        $documentRequest->load('user', 'transactionType');
        Notify::send($documentRequest->user, new DocumentRequestStatusNotification($documentRequest, 'rejected'));

        return back()->with('success', 'Request rejected.');
    }

    public function markClaimed(Request $request, DocumentRequest $documentRequest)
    {
        if ($documentRequest->status !== 'validated') {
            return back()->with('error', 'Only validated requests can be marked as claimed.');
        }

        $documentRequest->update([
            'status'     => 'claimed',
            'claimed_at' => now(),
        ]);

        return back()->with('success', 'Request marked as claimed.');
    }

    public function markPaid(DocumentRequest $documentRequest)
    {
        if ($documentRequest->payment_method !== 'cash' || $documentRequest->payment_status !== 'unpaid') {
            return back()->with('error', 'Only unpaid cash requests can be marked paid.');
        }

        $documentRequest->update(['payment_status' => 'paid']);

        $documentRequest->load('user', 'transactionType');
        Notify::send($documentRequest->user, new DocumentRequestStatusNotification($documentRequest, 'payment_confirmed'));

        return back()->with('success', 'Request marked as paid.');
    }
}