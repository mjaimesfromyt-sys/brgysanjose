<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function updatePhoto(Request $request)
    {
        $request->validate([
            'profile_picture' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $user = Auth::user();

        $targetDir = '/home/u370190562/domains/brgysanjose.site/public_html/uploads/profile_pictures';
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        if ($user->profile_picture) {
            $oldPath = '/home/u370190562/domains/brgysanjose.site/public_html/' . ltrim($user->profile_picture, '/');
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        $file = $request->file('profile_picture');
        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($targetDir, $fileName);

        $user->update([
            'profile_picture' => 'uploads/profile_pictures/' . $fileName
        ]);

        return redirect()->route('profile.edit')->with('status_photo', 'Your profile picture has been updated.');
    }

    public function removePhoto()
    {
        $user = Auth::user();

        if ($user->profile_picture) {
            $oldPath = '/home/u370190562/domains/brgysanjose.site/public_html/' . ltrim($user->profile_picture, '/');
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        $user->update(['profile_picture' => null]);

        return redirect()->route('profile.edit')->with('status_photo', 'Your profile picture has been removed.');
    }

    public function updateDetails(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'first_name'  => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name'   => ['required', 'string', 'max:100'],
            'suffix'      => ['nullable', 'string', 'max:20'],
            'contact_no'  => ['nullable', 'string', 'max:20'],
            'address'     => ['nullable', 'string', 'max:255'],
            'purok'       => ['nullable', 'string', 'max:100'],
        ]);

        $user->update($validated);

        return redirect()->route('profile.edit')->with('status_details', 'Your personal details have been updated successfully.');
    }

    /**
     * Set o Change Password (Auto-detects Google Login)
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        $isGoogleUser = !empty($user->google_id) || $user->registration_method === 'google';

        $rules = [
            'password' => ['required', 'confirmed', Password::min(8)],
        ];

        // Kung dili Google user, kinahanglan ang current_password
        if (!$isGoogleUser) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $request->validate($rules);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        $msg = $isGoogleUser 
            ? 'Password has been set successfully! You can now also log in using your email and password.' 
            : 'Your password has been changed successfully.';

        return redirect()->route('profile.edit')->with('status_password', $msg);
    }
}