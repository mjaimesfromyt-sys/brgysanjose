<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'    => ['required', 'string', 'max:255'],
            'middle_name'   => ['nullable', 'string', 'max:255'],
            'last_name'     => ['required', 'string', 'max:255'],
            'suffix'        => ['nullable', 'string', 'max:50'],
            'email'         => ['required', 'email', 'max:255', 'unique:users,email'],
            'contact_no'    => ['nullable', 'string', 'max:30'],
            'address'       => ['nullable', 'string', 'max:500'],
            'purok'         => ['nullable', 'string', 'max:100'],
            'declared_type' => ['required', 'in:resident,non_resident'],
            'password'      => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'first_name'        => $validated['first_name'],
            'middle_name'       => $validated['middle_name'] ?? null,
            'last_name'         => $validated['last_name'],
            'suffix'            => $validated['suffix'] ?? null,
            'email'             => $validated['email'],
            'contact_no'        => $validated['contact_no'] ?? null,
            'address'           => $validated['address'] ?? null,
            'purok'             => $validated['purok'] ?? null,
            'declared_type'     => $validated['declared_type'],
            'password'          => Hash::make($validated['password']),
            'role'              => 'resident',
            'status'            => 'pending',
            'email_verified_at' => now(),
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Welcome! Your account is pending review by the barangay. You will be able to make requests once approved.');
    }
}
