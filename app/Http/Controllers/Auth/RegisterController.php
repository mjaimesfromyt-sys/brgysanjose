<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\OtpVerificationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification as Notify;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register');
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $hasTurnstile = !empty(config('services.turnstile.secret_key')) && !empty(config('services.turnstile.site_key'));

        $rules = [
            'first_name'            => ['required', 'string', 'max:255'],
            'middle_name'           => ['nullable', 'string', 'max:255'],
            'last_name'             => ['required', 'string', 'max:255'],
            'suffix'                => ['nullable', 'string', 'max:50'],
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
            'contact_no'            => ['required', 'string', 'max:30'],
            'birthdate'             => ['required', 'date', 'before:today'],
            'gender'                => ['required', 'string', 'in:Male,Female'],
            'civil_status'          => ['required', 'string', 'in:Single,Married,Widowed,Separated'],
            'citizenship'           => ['nullable', 'string', 'max:100'],
            'religion'              => ['nullable', 'string', 'max:100'],
            'address'               => ['required', 'string', 'max:500'],
            'purok'                 => ['required', 'string', 'in:Purok 1,Purok 2,Purok 3,Purok 4,Purok 5,Purok 6,Purok 7'],
            'length_of_stay'        => ['nullable', 'string', 'max:100'],
            'is_voter'              => ['nullable', 'boolean'],
            'id_type'               => ['required', 'string', 'max:100'],
            'id_number'             => ['required', 'string', 'max:100'],
            'id_photo'              => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'declared_type'         => ['required', 'in:resident,non_resident'],
            'password'              => ['required', 'confirmed', Password::min(8)],
        ];

        // 👉 KINAHANGLANON LANG KON NAKA-SETUP ANG SECRET KEY SA .ENV
        if ($hasTurnstile) {
            $rules['cf-turnstile-response'] = ['required'];
        }

        $validated = $request->validate($rules, [
            'cf-turnstile-response.required' => 'Please complete the security verification checkbox.',
            'id_photo.required'              => 'Please upload a clear photo of your valid Government ID.',
        ]);

        // Cloudflare Turnstile Verification (kon active)
        if ($hasTurnstile && $request->filled('cf-turnstile-response')) {
            try {
                $turnstileResponse = Http::asForm()
                    ->timeout(10)
                    ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                        'secret'   => config('services.turnstile.secret_key'),
                        'response' => $request->input('cf-turnstile-response'),
                        'remoteip' => $request->ip(),
                    ])->json();

                if (! ($turnstileResponse['success'] ?? false)) {
                    return back()
                        ->withErrors(['cf-turnstile-response' => 'Security verification failed. Please try again.'])
                        ->withInput($request->except(['password', 'password_confirmation', 'cf-turnstile-response']));
                }
            } catch (\Throwable $e) {
                // Kon mag-timeout ang Cloudflare API, dili i-block ang residente
            }
        }

        // Handle Valid ID Photo Upload
        $idPhotoPath = null;
        if ($request->hasFile('id_photo')) {
            $file = $request->file('id_photo');
            $filename = 'id_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $file->getClientOriginalExtension();
            
            $dest1 = public_path('uploads/ids');
            if (!file_exists($dest1)) {
                mkdir($dest1, 0755, true);
            }
            $file->move($dest1, $filename);

            $dest2 = base_path('../public_html/uploads/ids');
            if (is_dir(base_path('../public_html'))) {
                if (!file_exists($dest2)) {
                    mkdir($dest2, 0755, true);
                }
                copy($dest1 . '/' . $filename, $dest2 . '/' . $filename);
            }

            // Backup copy INSIDE the app (laravel_app/public/uploads/ids).
            // The web root can be re-synced/redeployed without the app folder;
            // this copy survives that, and routes/id-photo-fallback serves it
            // whenever the public copy is missing.
            $dest3 = base_path('public/uploads/ids');
            if (!file_exists($dest3)) {
                mkdir($dest3, 0755, true);
            }
            if (rtrim($dest3, '/\\') !== rtrim($dest1, '/\\') && !file_exists($dest3 . '/' . $filename)) {
                copy($dest1 . '/' . $filename, $dest3 . '/' . $filename);
            }

            $idPhotoPath = 'uploads/ids/' . $filename;
        }

        $otpCode = (string) random_int(100000, 999999);

        $user = User::create([
            'first_name'            => $validated['first_name'],
            'middle_name'           => $validated['middle_name'] ?? null,
            'last_name'             => $validated['last_name'],
            'suffix'                => $validated['suffix'] ?? null,
            'email'                 => $validated['email'],
            'contact_no'            => $validated['contact_no'],
            'birthdate'             => $validated['birthdate'],
            'gender'                => $validated['gender'],
            'civil_status'          => $validated['civil_status'],
            'citizenship'           => $validated['citizenship'] ?? 'Filipino',
            'religion'              => $validated['religion'] ?? 'Roman Catholic',
            'address'               => $validated['address'],
            'purok'                 => $validated['purok'],
            'length_of_stay'        => $validated['length_of_stay'] ?? null,
            'is_voter'              => $request->boolean('is_voter'),
            'id_type'               => $validated['id_type'],
            'id_number'             => $validated['id_number'],
            'id_photo_front'        => $idPhotoPath,
            'declared_type'         => $validated['declared_type'],
            'password'              => Hash::make($validated['password']),
            'role'                  => 'resident',
            'status'                => 'pending',
            'email_verified_at'     => null,
            'email_otp_hash'        => Hash::make($otpCode),
            'email_otp_expires_at'  => now()->addMinutes(10),
            'email_otp_attempts'    => 0,
        ]);

        Notify::send($user, new OtpVerificationNotification($otpCode, trim($user->first_name . ' ' . $user->last_name)));

        session(['otp_user_id' => $user->id]);

        return redirect()->route('otp.verify.form')
            ->with('success', 'We sent a 6-digit verification code to your email.');
    }
}
