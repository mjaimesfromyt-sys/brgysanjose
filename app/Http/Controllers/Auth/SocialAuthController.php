<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }


    public function handleGoogleCallback()
    {
    $googleUser = Socialite::driver('google')->stateless()->user();

    $nameParts = explode(' ', trim($googleUser->getName()));

    $firstName = $nameParts[0] ?? 'Google';

    $lastName = count($nameParts) > 1
        ? implode(' ', array_slice($nameParts, 1))
        : 'User';


    $user = User::updateOrCreate(
        [
            'email' => $googleUser->getEmail(),
        ],
        [
            'first_name' => $firstName,
            'last_name' => $lastName,

            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),

            'registration_method' => 'google',
            'password' => bcrypt(str()->random(32)),

            'email_verified_at' => now(),

            'role' => 'resident',
            'status' => 'pending',
        ]
    );


    Auth::login($user);

    request()->session()->regenerate();


    return redirect()
        ->route('dashboard')
        ->with(
            'success',
            'Google login successful. Your account is pending barangay approval.'
        );
}
}