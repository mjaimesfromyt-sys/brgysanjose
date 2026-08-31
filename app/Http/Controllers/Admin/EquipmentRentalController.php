<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\EquipmentRental;
use App\Notifications\EquipmentRentalStatusNotification;
use App\Support\ClaimCode;
use App\Support\Notify;
use Illuminate\Http\Request;

class EquipmentRentalController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $tabs = [
            'pending'  => 'Pending',
            'approved' => 'Approved',
            'released' => 'Released',
            'returned' => 'Returned',
            'rejected' => 'Rejected',
        ];

        $rentals = EquipmentRental::with(['user', 'items.equipment'])
            ->when(array_key_exists($status, $tabs), fn ($q) => $q->where('status', $status))
            ->orderBy('start_date')
            ->get();

        $counts = collect(array_keys($tabs))
            ->mapWithKeys(fn ($key) => [$key => EquipmentRental::where('status', $key)->count()])
            ->all();

        return view('admin.rentals.index', compact('rentals', 'status', 'counts', 'tabs'));
    }

    public function approve(Request $request, EquipmentRental $rental)
    {
        if ($rental->status !== 'pending') {
            return back()->with('error', 'This rental has already been reviewed.');
        }

        foreach ($rental->items as $line) {
            $available = $line->equipment->availableFor(
                $rental->start_date->format('Y-m-d'),
                $rental->end_date->format('Y-m-d'),
                $rental->id
            );

            if ($line->quantity > $available) {
                return back()->with('error', "Cannot approve: only {$available} {$line->equipment->name}(s) available for these dates.");
            }
        }

        $rental->update([
            'status'      => 'approved',
            'reviewed_by' => $request->user()->id,
            'claim_code'  => $rental->claim_code ?? ClaimCode::next('equipment_rentals'),
        ]);

        $rental->load('user', 'items.equipment');
        Notify::send($rental->user, new EquipmentRentalStatusNotification($rental, 'approved'));

        return back()->with('success', 'Rental approved.');
    }

    public function reject(Request $request, EquipmentRental $rental)
    {
        $validated = $request->validate([
            'admin_remarks' => ['nullable', 'string', 'max:500'],
        ]);

        if ($rental->status !== 'pending') {
            return back()->with('error', 'This rental has already been reviewed.');
        }

        if ($rental->payment_status === 'paid') {
            $rental->restoreStock();
        }

        $rental->update([
            'status'        => 'rejected',
            'reviewed_by'   => $request->user()->id,
            'admin_remarks' => $validated['admin_remarks'] ?? null,
        ]);

        $rental->load('user', 'items.equipment');
        Notify::send($rental->user, new EquipmentRentalStatusNotification($rental, 'rejected'));

        return back()->with('success', 'Rental rejected.');
    }

    public function release(Request $request, EquipmentRental $rental)
    {
        if ($rental->status !== 'approved') {
            return back()->with('error', 'Only approved rentals can be marked as released.');
        }

        $rental->update([
            'status'      => 'released',
            'released_at' => now(),
            'reviewed_by' => $request->user()->id,
        ]);

        $rental->load('user', 'items.equipment');
        Notify::send($rental->user, new EquipmentRentalStatusNotification($rental, 'released'));

        return back()->with('success', 'Equipment marked as released to resident.');
    }

    public function markReturned(Request $request, EquipmentRental $rental)
    {
        if ($rental->status !== 'released') {
            return back()->with('error', 'Only released rentals can be marked as returned.');
        }

        if ($rental->payment_status === 'paid') {
            $rental->restoreStock();
        }

        $rental->update([
            'status'      => 'returned',
            'returned_at' => now(),
            'reviewed_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Equipment marked as returned.');
    }

    public function markPaid(EquipmentRental $rental)
    {
        if ($rental->payment_method !== 'cash' || $rental->payment_status !== 'unpaid') {
            return back()->with('error', 'Only unpaid cash rentals can be marked paid.');
        }

        $rental->update(['payment_status' => 'paid']);
        $rental->deductStock();

        $rental->load('user', 'items.equipment');
        Notify::send($rental->user, new EquipmentRentalStatusNotification($rental, 'payment_confirmed'));

        return back()->with('success', 'Rental marked as paid. Stock has been deducted.');
    }
}
