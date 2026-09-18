<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\ResidentApprovedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ResidentController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $users = User::where('role', 'resident')
            ->when(
                in_array($status, ['pending', 'active', 'rejected']),
                fn ($q) => $q->where('status', $status)
            )
            ->orderBy('created_at', 'desc')
            ->get();

        $counts = [
            'pending'  => User::where('role', 'resident')->where('status', 'pending')->count(),
            'active'   => User::where('role', 'resident')->where('status', 'active')->count(),
            'rejected' => User::where('role', 'resident')->where('status', 'rejected')->count(),
        ];

        return view('admin.residents.index', compact('users', 'status', 'counts'));
    }

    public function approve(Request $request, User $user)
    {
        $validated = $request->validate([
            'resident_type' => ['required', 'in:resident,non_resident'],
        ]);

        $oldStatus = $user->status;
        $oldResidentType = $user->resident_type;

        $user->update([
            'status'        => 'active',
            'resident_type' => $validated['resident_type'],
            'verified_at'   => now(),
            'verified_by'   => $request->user()->id,
        ]);

        activity('residents')
            ->causedBy($request->user())
            ->performedOn($user)
            ->withProperties([
                'action'            => 'approved',
                'old_status'        => $oldStatus,
                'new_status'        => 'active',
                'old_resident_type' => $oldResidentType,
                'new_resident_type' => $validated['resident_type'],
            ])
            ->log('Resident account approved');

        // 👉 Awtomatikong magpadala og Approval Email
        $emailSent = false;
        if (!empty($user->email)) {
            try {
                Mail::to($user->email)->send(new ResidentApprovedMail($user));
                $emailSent = true;
            } catch (\Throwable $e) {
                Log::error('Failed to send resident approval email to ' . $user->email . ': ' . $e->getMessage());
            }
        }

        $msg = "{$user->name} approved as " . ($validated['resident_type'] === 'resident' ? 'a verified resident.' : 'a non-resident.');
        if ($emailSent) {
            $msg .= " An official approval email notification has been sent to {$user->email}.";
        }

        return back()->with('success', $msg);
    }

    public function reject(Request $request, User $user)
    {
        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $oldStatus = $user->status;

        $user->update([
            'status'           => 'rejected',
            'verified_at'      => now(),
            'verified_by'      => $request->user()->id,
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);

        activity('residents')
            ->causedBy($request->user())
            ->performedOn($user)
            ->withProperties([
                'action'     => 'rejected',
                'old_status' => $oldStatus,
                'new_status' => 'rejected',
            ])
            ->log('Resident account rejected');

        return back()->with('success', "{$user->name}'s registration was rejected.");
    }

    public function reconsider(Request $request, User $user)
    {
        $validated = $request->validate([
            'resident_type' => ['required', 'in:resident,non_resident'],
        ]);

        $oldStatus = $user->status;
        $oldResidentType = $user->resident_type;

        $user->update([
            'status'           => 'active',
            'resident_type'    => $validated['resident_type'],
            'verified_at'      => now(),
            'verified_by'      => $request->user()->id,
            'rejection_reason' => null,
        ]);

        activity('residents')
            ->causedBy($request->user())
            ->performedOn($user)
            ->withProperties([
                'action'            => 'reconsidered',
                'old_status'        => $oldStatus,
                'new_status'        => 'active',
                'old_resident_type' => $oldResidentType,
                'new_resident_type' => $validated['resident_type'],
            ])
            ->log('Resident account approved on reconsideration');

        if (!empty($user->email)) {
            try {
                Mail::to($user->email)->send(new ResidentApprovedMail($user));
            } catch (\Throwable $e) {
                Log::error('Failed to send approval email on reconsideration: ' . $e->getMessage());
            }
        }

        return back()->with('success', "{$user->name} has been approved on reconsideration.");
    }
}
