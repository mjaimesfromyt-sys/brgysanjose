<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\ProfilePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * "My Profile" — a resident maintaining their own account details.
 *
 * Everything here acts on the signed-in user only; nothing takes an id from
 * the request, so there is no record to authorise against. The one exception
 * is photo(), which serves someone else's image to barangay staff.
 */
class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', ['me' => Auth::user()]);
    }

    /**
     * Name, contact number and address.
     *
     * Email is deliberately not editable: it is the login and was verified by
     * OTP at registration, so changing it needs a re-verification flow rather
     * than a text field.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'first_name'  => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name'   => ['required', 'string', 'max:255'],
            'suffix'      => ['nullable', 'string', 'max:50'],
            'contact_no'  => ['nullable', 'string', 'max:30'],
            'address'     => ['nullable', 'string', 'max:500'],
            'purok'       => ['nullable', 'string', 'max:100'],
        ]);

        $request->user()->fill($validated)->save();

        return redirect()->route('profile.edit')
            ->with('success', 'Your profile has been updated.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.current_password' => 'That is not your current password.',
        ]);

        $user = $request->user();
        $user->password = $request->input('password');
        $user->save();

        // The password just changed, so every other session holding this
        // account is re-authenticated against the new hash and dropped.
        Auth::logoutOtherDevices($request->input('password'));
        $request->session()->regenerate();

        return redirect()->route('profile.edit')
            ->with('success', 'Your password has been changed.');
    }

    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ], [
            'photo.required' => 'Please choose a photo. If you did pick one, it may be larger than the server accepts.',
            'photo.max'      => 'That photo is larger than 5 MB. Please choose a smaller one.',
            'photo.mimes'    => 'Please upload a JPG, PNG or WebP image.',
        ]);

        $user = $request->user();

        try {
            $path = ProfilePhoto::store($request->file('photo'), $user);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['photo' => $e->getMessage()]);
        }

        $user->avatar_path = $path;
        $user->save();

        return redirect()->route('profile.edit')
            ->with('success', 'Your profile picture has been updated.');
    }

    public function destroyPhoto(Request $request)
    {
        $user = $request->user();

        ProfilePhoto::delete($user->avatar_path);

        $user->avatar_path = null;
        $user->save();

        return redirect()->route('profile.edit')
            ->with('success', 'Your profile picture has been removed.');
    }

    /**
     * Stream a profile photo.
     *
     * Photos live outside the web root — the live site's root .htaccess denies
     * /storage outright — so they are served from here instead. A resident can
     * only fetch their own; barangay staff can fetch any, which is what lets
     * the admin resident list show them.
     */
    public function photo(Request $request, User $user): StreamedResponse
    {
        $viewer = $request->user();

        abort_unless(
            $viewer->is($user) || $viewer->isAdmin() || $viewer->isOfficial(),
            403,
        );

        abort_unless($user->hasAvatar() && Storage::disk('local')->exists($user->avatar_path), 404);

        return Storage::disk('local')->response($user->avatar_path, 'photo.jpg', [
            'Content-Type'  => 'image/jpeg',
            // The filename changes on every upload, so a long private cache is
            // safe and keeps repeat page loads off PHP.
            'Cache-Control' => 'private, max-age=604800',
        ]);
    }
}
