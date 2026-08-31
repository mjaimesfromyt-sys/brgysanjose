<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ResidentController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $users = User::where('role', 'resident')
            ->when(in_array($status, ['pending', 'active', 'rejected']),
                fn ($q) => $q->where('status', $status))
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

        $user->update([
            'status'        => 'active',
            'resident_type' => $validated['resident_type'],
            'verified_at'   => now(),
            'verified_by'   => $request->user()->id,
        ]);

        return back()->with('success', "{$user->name} approved as " .
            ($validated['resident_type'] === 'resident' ? 'a verified resident.' : 'a non-resident.'));
    }

    public function reject(Request $request, User $user)
    {
        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $user->update([
            'status'           => 'rejected',
            'verified_at'      => now(),
            'verified_by'      => $request->user()->id,
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);

        return back()->with('success', "{$user->name}'s registration was rejected.");
    }
    public function reconsider(Request $request, User $user)
    {
        $validated = $request->validate([
            'resident_type' => ['required', 'in:resident,non_resident'],
        ]);

        $user->update([
            'status'           => 'active',
            'resident_type'    => $validated['resident_type'],
            'verified_at'      => now(),
            'verified_by'      => $request->user()->id,
            'rejection_reason' => null,
        ]);

        return back()->with('success', "{$user->name} has been approved on reconsideration.");
    }
}