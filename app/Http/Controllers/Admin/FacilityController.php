<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    public function index()
    {
        $facilities = Facility::orderBy('name')->get();
        return view('admin.facilities.index', compact('facilities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:facilities,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'capacity'    => ['nullable', 'integer', 'min:1'],
            'fee'         => ['nullable', 'numeric', 'min:0'],
        ]);

        Facility::create($validated + ['is_active' => true]);

        return back()->with('success', 'Facility added.');
    }

    public function toggle(Facility $facility)
    {
        $facility->update(['is_active' => ! $facility->is_active]);
        return back()->with('success', 'Facility status updated.');
    }
}