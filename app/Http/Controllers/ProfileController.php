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

        // InfinityFree/wuaze: open_basedir only allows the htdocs tree, so all
        // paths must be derived (public_path/base_path), never hardcoded to a
        // previous host. Mirror the proven ID-photo upload strategy:
        // write to the app's public dir, copy to the web root when possible,
        // and let the /uploads/profile_pictures/{filename} fallback route
        // serve whichever copy exists.
        $this->deletePhotoFiles($user->profile_picture);

        $file = $request->file('profile_picture');
        $fileName = 'pp_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $file->getClientOriginalExtension();

        $dest1 = public_path('uploads/profile_pictures');
        if (!file_exists($dest1)) {
            mkdir($dest1, 0755, true);
        }
        $file->move($dest1, $fileName);

        // Secondary copy in the web root (/htdocs/uploads/profile_pictures) so
        // the image also resolves as a static file without PHP routing.
        $dest2 = base_path('../public_html/uploads/profile_pictures');
        if (is_dir(base_path('../public_html'))) {
            if (!file_exists($dest2)) {
                @mkdir($dest2, 0755, true);
            }
            @copy($dest1 . '/' . $fileName, $dest2 . '/' . $fileName);
        }

        $user->update([
            'profile_picture' => 'uploads/profile_pictures/' . $fileName
        ]);

        return redirect()->route('profile.edit')->with('status_photo', 'Your profile picture has been updated.');
    }

    public function removePhoto()
    {
        $user = Auth::user();

        $this->deletePhotoFiles($user->profile_picture);

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

    /**
     * Delete every on-disk copy of a stored profile_picture path
     * (web root + app public backup). Tolerates legacy paths.
     */
    private function deletePhotoFiles(?string $storedPath): void
    {
        if (empty($storedPath)) {
            return;
        }

        $filename = basename($storedPath);

        $candidates = [
            public_path('uploads/profile_pictures/' . $filename),        // laravel_app/public/...
            base_path('../public_html/uploads/profile_pictures/' . $filename), // web root
        ];

        foreach ($candidates as $path) {
            try {
                if (is_file($path)) {
                    @unlink($path);
                }
            } catch (\Throwable $e) {
                // open_basedir on a foreign path just means "not ours" — skip.
            }
        }
    }
}
