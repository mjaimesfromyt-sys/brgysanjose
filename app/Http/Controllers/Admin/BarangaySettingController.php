<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangaySettingController extends Controller
{
    public function index()
    {
        $settings = DB::table('barangay_settings')->pluck('value', 'key')->toArray();
        return view('admin.settings.officials', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'captain_name'      => 'required|string|max:150',
            'secretary_name'    => 'required|string|max:150',
            'barangay_address'  => 'required|string|max:255',
            'barangay_email'    => 'required|email|max:150',
            'barangay_facebook' => 'required|string|max:150',
        ]);

        foreach ($validated as $key => $value) {
            DB::table('barangay_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now()]
            );
        }

        return back()->with('success', 'Official Barangay settings updated! All printed certificates will now use these new details.');
    }
}