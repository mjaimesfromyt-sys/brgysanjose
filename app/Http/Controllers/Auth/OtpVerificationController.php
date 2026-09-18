<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\OtpVerificationNotification;
use App\Support\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class OtpVerificationController extends Controller
{
    public function show(Request $request)
    {
        $userId = $request->session()->get('otp_user_id');

        if (! $userId) {
            return redirect()->route('register')
                ->withErrors([
                    'email' => 'Please register first before verifying your email.',
                ]);
        }

        $user = User::find($userId);

        if (! $user) {
            $request->session()->forget('otp_user_id');

            return redirect()->route('register')
                ->withErrors([
                    'email' => 'Unable to find the account awaiting verification.',
                ]);
        }

        if ($user->email_verified_at) {
            return redirect()->route('login')
                ->with('success', 'Your email is already verified. You may log in.');
        }

        return view('auth.verify-otp', compact('user'));
    }

    public function verify(Request $request)
    {
        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $userId = $request->session()->get('otp_user_id');

        if (! $userId) {
            return redirect()->route('register')
                ->withErrors([
                    'email' => 'Your verification session has expired. Please register again.',
                ]);
        }

        $user = User::find($userId);

        if (! $user) {
            $request->session()->forget('otp_user_id');

            return redirect()->route('register')
                ->withErrors([
                    'email' => 'Unable to find the account awaiting verification.',
                ]);
        }

        if ($user->email_verified_at) {
            return redirect()->route('login')
                ->with('success', 'Your email is already verified. You may log in.');
        }

        if (
            ! $user->email_otp_expires_at ||
            now()->greaterThan($user->email_otp_expires_at)
        ) {
            return back()
                ->withErrors([
                    'otp' => 'This verification code has expired. Please request a new code.',
                ]);
        }

        if ($user->email_otp_attempts >= 5) {
            return back()
                ->withErrors([
                    'otp' => 'Too many incorrect attempts. Please request a new verification code.',
                ]);
        }

        if (! Hash::check($validated['otp'], $user->email_otp_hash)) {
            $user->increment('email_otp_attempts');

            return back()
                ->withErrors([
                    'otp' => 'The verification code is incorrect.',
                ]);
        }

        $user->forceFill([
            'email_verified_at' => now(),
            'email_otp_hash' => null,
            'email_otp_expires_at' => null,
            'email_otp_attempts' => 0,
        ])->save();

        $request->session()->forget('otp_user_id');

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('dashboard')
             ->with([
             'success' => 'Your email has been verified successfully. Your account is still pending barangay approval.',
            'ga_event' => 'sign_up',
        ]);
    }

    public function resend(Request $request)
    {
        $userId = $request->session()->get('otp_user_id');

        if (! $userId) {
            return redirect()->route('register')
                ->withErrors([
                    'email' => 'Your verification session has expired. Please register again.',
                ]);
        }

        $user = User::find($userId);

        if (! $user) {
            $request->session()->forget('otp_user_id');

            return redirect()->route('register');
        }

        if ($user->email_verified_at) {
            return redirect()->route('login')
                ->with('success', 'Your email is already verified.');
        }

        $otpCode = (string) random_int(100000, 999999);

        $user->forceFill([
            'email_otp_hash' => Hash::make($otpCode),
            'email_otp_expires_at' => now()->addMinutes(10),
            'email_otp_attempts' => 0,
        ])->save();

        Notify::send(
            $user,
            new OtpVerificationNotification(
                $otpCode,
                trim($user->first_name . ' ' . $user->last_name)
            )
        );

        return back()
            ->with('success', 'A new verification code has been sent to your email.');
    }
}