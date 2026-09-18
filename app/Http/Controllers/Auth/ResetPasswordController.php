<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    public function show(Request $request, string $token)
    {
        // The route is wrapped in the signed middleware; double-check the
        // email parameter exists so the form is always complete.
        $email = (string) $request->query('email', '');
        abort_unless($email !== '', 404);

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token'    => ['required', 'string'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $validated['email'])
            ->first();

        $tokenMatches = $record !== null
            && Hash::check($validated['token'], $record->token)
            && now()->diffInMinutes($record->created_at) <= 60;

        if (! $tokenMatches) {
            return back()
                ->with('status', 'This reset link is invalid or has expired. Please request a new one.')
                ->withInput($request->only('email'));
        }

        // Only users who can actually log in with a password.
        $user = User::where('email', $validated['email'])
            ->whereNull('google_id')
            ->whereNull('apple_id')
            ->first();

        if ($user === null) {
            return back()->with('status', 'This account cannot reset its password here.');
        }

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'remember_token' => Str::random(60),
        ])->save();

        DB::table('password_reset_tokens')
            ->where('email', $validated['email'])
            ->delete();

        return redirect()->route('login')
            ->with('success', 'Your password has been reset. You can now log in with your new password.');
    }
}
