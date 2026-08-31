<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function index()
    {
        $equipment = Equipment::orderBy('name')->get();

        return view('admin.equipment.index', compact('equipment'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:equipment,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'fee'         => ['nullable', 'numeric', 'min:0'],
            'total_stock' => ['required', 'integer', 'min:0'],
        ]);

        Equipment::create($validated + ['is_active' => true]);

        return back()->with('success', 'Equipment item added.');
    }

    public function toggle(Equipment $equipment)
    {
        $equipment->update(['is_active' => ! $equipment->is_active]);

        return back()->with('success', 'Equipment status updated.');
    }
}
