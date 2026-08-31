<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TransactionType;
use Illuminate\Http\Request;

class TransactionTypeController extends Controller
{
    public function index()
    {
        $types = TransactionType::withCount('requirements')->orderBy('name')->get();
        return view('admin.transactions.index', compact('types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:255', 'unique:transaction_types,name'],
            'description'        => ['nullable', 'string', 'max:1000'],
            'fee'                => ['nullable', 'numeric', 'min:0'],
            'requires_residency' => ['nullable', 'boolean'],
        ]);

        TransactionType::create([
            'name'               => $validated['name'],
            'description'        => $validated['description'] ?? null,
            'fee'                => $validated['fee'] ?? null,
            'requires_residency' => $request->boolean('requires_residency'),
            'is_active'          => true,
        ]);

        return back()->with('success', 'Transaction type added.');
    }

    public function edit(TransactionType $transactionType)
    {
        $transactionType->load('requirements');
        return view('admin.transactions.edit', compact('transactionType'));
    }

    public function update(Request $request, TransactionType $transactionType)
    {
        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:255', 'unique:transaction_types,name,' . $transactionType->id],
            'description'        => ['nullable', 'string', 'max:1000'],
            'fee'                => ['nullable', 'numeric', 'min:0'],
            'requires_residency' => ['nullable', 'boolean'],
            'is_active'          => ['nullable', 'boolean'],
        ]);

        $transactionType->update([
            'name'               => $validated['name'],
            'description'        => $validated['description'] ?? null,
            'fee'                => $validated['fee'] ?? null,
            'requires_residency' => $request->boolean('requires_residency'),
            'is_active'          => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Transaction type updated.');
    }

    public function addRequirement(Request $request, TransactionType $transactionType)
    {
        $validated = $request->validate([
            'item' => ['required', 'string', 'max:255'],
        ]);

        $transactionType->requirements()->create($validated);
        return back()->with('success', 'Requirement added.');
    }

    public function deleteRequirement(TransactionType $transactionType, \App\Models\Requirement $requirement)
    {
        abort_unless($requirement->transaction_type_id === $transactionType->id, 404);
        $requirement->delete();
        return back()->with('success', 'Requirement removed.');
    }
}