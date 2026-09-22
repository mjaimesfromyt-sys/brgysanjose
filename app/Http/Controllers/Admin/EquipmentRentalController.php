<?php

namespace App\Http\Controllers\Admin;
use App\Events\ResidentStatusUpdatedEvent;

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

        // Dili maka-approve kon wala pa mabayri
        if ($rental->payment_status === 'unpaid') {
            return back()->with(
                'error',
                'Cannot approve rental: Payment must be confirmed or marked as paid first.'
            );
        }

        // 👉 Susiha lang ang stock kon wala pa na-deduct daan sa payment
        if ($rental->payment_status !== 'paid') {
            foreach ($rental->items as $line) {
                $available = $line->equipment->availableFor(
                    $rental->start_date->format('Y-m-d'),
                    $rental->end_date->format('Y-m-d'),
                    $rental->id
                );

                if ($line->quantity > $available) {
                    return back()->with(
                        'error',
                        "Cannot approve: only {$available} {$line->equipment->name}(s) available for these dates."
                    );
                }
            }
        }

        $oldStatus = $rental->status;

        $rental->update([
            'status'      => 'approved',
            'reviewed_by' => $request->user()->id,
            'claim_code'  => $rental->claim_code ?? ClaimCode::next('equipment_rentals'),
        ]);

        activity('equipment_rentals')
            ->causedBy($request->user())
            ->performedOn($rental)
            ->withProperties([
                'action'     => 'approved',
                'old_status' => $oldStatus,
                'new_status' => 'approved',
            ])
            ->log('Equipment rental approved');

        $rental->load('user', 'items.equipment');

        Notify::send(
            $rental->user,
            new EquipmentRentalStatusNotification($rental, 'approved')
        );

        return back()->with('success', 'Rental approved successfully.');
    }

    public function reject(Request $request, EquipmentRental $rental)
    {
        $validated = $request->validate([
            'admin_remarks' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        if ($rental->status !== 'pending') {
            return back()->with('error', 'This rental has already been reviewed.');
        }

        // 👉 Paid rentals can no longer be rejected — route the money matter
        // through the Refunds module instead (audit-safe cash handling).
        if ($rental->payment_status === 'paid') {
            return back()->with('error', 'This rental is already paid. Use the Refunds module to cancel and refund it.');
        }

        $oldStatus = $rental->status;
        $oldPaymentStatus = $rental->payment_status;

        if ($rental->payment_status === 'paid') {
            $rental->restoreStock();
        }

        $rental->update([
            'status'        => 'rejected',
            'reviewed_by'   => $request->user()->id,
            'admin_remarks' => $validated['admin_remarks'] ?? null,
        ]);

        activity('equipment_rentals')
            ->causedBy($request->user())
            ->performedOn($rental)
            ->withProperties([
                'action'         => 'rejected',
                'old_status'     => $oldStatus,
                'new_status'     => 'rejected',
                'payment_status' => $oldPaymentStatus,
                'stock_restored' => $oldPaymentStatus === 'paid',
            ])
            ->log('Equipment rental rejected');

        $rental->load('user', 'items.equipment');

        Notify::send(
            $rental->user,
            new EquipmentRentalStatusNotification($rental, 'rejected')
        );

        return back()->with('success', 'Rental rejected.');
    }

    public function release(Request $request, EquipmentRental $rental)
    {
        if ($rental->status !== 'approved') {
            return back()->with(
                'error',
                'Only approved rentals can be marked as released.'
            );
        }

        $oldStatus = $rental->status;

        $rental->update([
            'status'      => 'released',
            'released_at' => now(),
            // Due sa katapusan nga adlaw sa rental, alas-5 PM (barangay office hours)
            'due_at'      => \Carbon\Carbon::parse($rental->end_date)->setTime(17, 0),
            'reviewed_by' => $request->user()->id,
        ]);

        activity('equipment_rentals')
            ->causedBy($request->user())
            ->performedOn($rental)
            ->withProperties([
                'action'     => 'released',
                'old_status' => $oldStatus,
                'new_status' => 'released',
            ])
            ->log('Equipment rental released');

        $rental->load('user', 'items.equipment');

        Notify::send(
            $rental->user,
            new EquipmentRentalStatusNotification($rental, 'released')
        );

        return back()->with(
            'success',
            'Equipment marked as released to resident.'
        );
    }

    public function markReturned(Request $request, EquipmentRental $rental)
    {
        if ($rental->status !== 'released') {
            return back()->with(
                'error',
                'Only released rentals can be marked as returned.'
            );
        }

        $oldStatus = $rental->status;
        $oldPaymentStatus = $rental->payment_status;

        $rental->update([
            'status'      => 'returned',
            'returned_at' => now(),
            'reviewed_by' => $request->user()->id,
        ]);

        $rental->restoreStock();

        activity('equipment_rentals')
            ->causedBy($request->user())
            ->performedOn($rental)
            ->withProperties([
                'action'         => 'returned',
                'old_status'     => $oldStatus,
                'new_status'     => 'returned',
                'payment_status' => $oldPaymentStatus,
                'stock_restored' => $oldPaymentStatus === 'paid',
            ])
            ->log('Equipment rental returned');

        return back()->with('success', 'Equipment marked as returned.');
    }

    public function markPaid(Request $request, EquipmentRental $rental)
    {
        if (
            $rental->payment_method !== 'cash' ||
            $rental->payment_status !== 'unpaid'
        ) {
            return back()->with(
                'error',
                'Only unpaid cash rentals can be marked paid.'
            );
        }

        $oldPaymentStatus = $rental->payment_status;

        $rental->update([
            'payment_status' => 'paid',
            'collected_by'   => $request->user()->id,
            'collected_at'   => now(),
        ]);

        $rental->deductStock();

        activity('equipment_rentals')
            ->causedBy($request->user())
            ->performedOn($rental)
            ->withProperties([
                'action'             => 'payment_marked_paid',
                'payment_method'     => 'cash',
                'old_payment_status' => $oldPaymentStatus,
                'new_payment_status' => 'paid',
                'stock_deducted'     => true,
            ])
            ->log('Equipment rental cash payment marked as paid');

        $rental->load('user', 'items.equipment');

        Notify::send(
            $rental->user,
            new EquipmentRentalStatusNotification($rental, 'payment_confirmed')
        );

        return back()->with(
            'success',
            'Rental marked as paid. Stock has been deducted.'
        );
    }
}
