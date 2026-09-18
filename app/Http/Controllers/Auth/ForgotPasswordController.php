<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function show()
    {
        return view('auth.forgot-password');
    }

    public function emailResetLink(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        // A fixed success response for unknown addresses so we never reveal
        // which emails have an account (user enumeration protection).
        Password::broker()->sendResetLink(
            ['email' => $validated['email']],
            function ($user, $token) {
                $user->notify(new ResetPasswordNotification($token, $user->email));
            }
        );

        return back()->with(
            'status',
            'If that email address is registered, a password reset link has been sent. Please check your inbox.'
        );
    }
}
