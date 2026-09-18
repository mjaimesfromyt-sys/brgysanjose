<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class StaffController extends Controller
{
    public function index()
    {
        $staffMembers = User::whereIn('role', ['admin', 'super_admin'])
            ->orderByRaw("FIELD(role, 'super_admin', 'admin')")
            ->latest()
            ->paginate(15);

        return view('admin.staff.index', compact('staffMembers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'contact_no' => ['nullable', 'string', 'max:20'],
            'position'   => ['nullable', 'string', 'max:100'], // Secretary, Treasurer, etc.
            'password'   => ['required', Password::min(8)],
            'role'       => ['required', 'in:admin,super_admin'],
        ]);

        User::create([
            'first_name'          => $validated['first_name'],
            'last_name'           => $validated['last_name'],
            'email'               => $validated['email'],
            'contact_no'          => $validated['contact_no'] ?? null,
            'position'            => $validated['position'] ?? 'Barangay Staff',
            'password'            => Hash::make($validated['password']),
            'role'                => $validated['role'],
            'status'              => 'active',
            'registration_method' => 'email',
            'email_verified_at'   => now(),
        ]);

        return redirect()->route('admin.staff.index')->with('success', 'New staff account created successfully!');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'contact_no' => ['nullable', 'string', 'max:20'],
            'position'   => ['nullable', 'string', 'max:100'],
            'password'   => ['nullable', Password::min(8)],
            'status'     => ['required', 'in:active,inactive'],
        ]);

        $data = [
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'email'      => $validated['email'],
            'contact_no' => $validated['contact_no'] ?? null,
            'position'   => $validated['position'] ?? $user->position,
            'status'     => $validated['status'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('admin.staff.index')->with('success', 'Staff credentials and position updated successfully!');
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        return back()->with('success', "Account status changed to {$newStatus}!");
    }
}
