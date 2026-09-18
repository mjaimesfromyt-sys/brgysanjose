<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        // Validate login fields and require a Cloudflare Turnstile token.
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'cf-turnstile-response' => ['required'],
        ], [
            'cf-turnstile-response.required' => 'Please complete the security verification.',
        ]);

        // Verify the Turnstile token directly with Cloudflare.
        try {
            $turnstileResponse = Http::asForm()
                ->timeout(10)
                ->post(
                    'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                    [
                        'secret' => config('services.turnstile.secret_key'),
                        'response' => $request->input('cf-turnstile-response'),
                        'remoteip' => $request->ip(),
                    ]
                )
                ->json();
        } catch (\Throwable $e) {
            return back()
                ->withErrors([
                    'cf-turnstile-response' => 'Security verification is temporarily unavailable. Please try again.',
                ])
                ->onlyInput('email');
        }

        // Stop the login if Cloudflare rejects the token.
        if (! ($turnstileResponse['success'] ?? false)) {
            return back()
                ->withErrors([
                    'cf-turnstile-response' => 'Security verification failed. Please try again.',
                ])
                ->onlyInput('email');
        }

        // Turnstile is not part of the user's login credentials.
        unset($credentials['cf-turnstile-response']);

        // Attempt normal Laravel authentication.
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Logged in successfully.');
        }

        return back()
            ->withErrors([
                'email' => 'These credentials do not match our records.',
            ])
            ->onlyInput('email');
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'You have been logged out.');
    }
}