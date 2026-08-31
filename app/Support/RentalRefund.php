<?php

namespace App\Support;

use App\Models\EquipmentRental;
use App\Services\PayMongoService;

class RentalRefund
{
    /**
     * Work out how much of a paid rental is refundable.
     *
     * Policy (agreed with the barangay):
     *  - Only the rental fee is refundable. The cashless transaction fee is
     *    kept because it was already paid out to PayMongo. Cash rentals have
     *    no transaction fee, so they refund in full.
     *  - Before the equipment is released  -> the whole rental fee.
     *  - After release (early return)       -> the rental fee prorated by the
     *    number of unused days (whole days, resident's favour).
     *
     * @return array{type:string, rental_fee:float, total_days:int, used_days:int, unused_days:int, refundable:float}
     */
    public static function estimate(EquipmentRental $rental): array
    {
        $rentalFee = (float) $rental->amount_due;
        if ($rental->payment_method !== 'cash') {
            $rentalFee -= PayMongoService::transactionFee();
        }
        $rentalFee = max($rentalFee, 0);

        $start = $rental->start_date->copy()->startOfDay();
        $end   = $rental->end_date->copy()->startOfDay();
        $totalDays = $start->diffInDays($end) + 1;

        if ($rental->status === 'released') {
            $today   = now()->startOfDay();
            $usedDays = $start->diffInDays($today) + 1;
            $usedDays = min(max($usedDays, 1), $totalDays);
            $unusedDays = $totalDays - $usedDays;
            $refundable = round($rentalFee * $unusedDays / $totalDays, 2);
            $type = 'early_return';
        } else {
            $usedDays   = 0;
            $unusedDays = $totalDays;
            $refundable = round($rentalFee, 2);
            $type = 'cancellation';
        }

        return [
            'type'        => $type,
            'rental_fee'  => round($rentalFee, 2),
            'total_days'  => $totalDays,
            'used_days'   => $usedDays,
            'unused_days' => $unusedDays,
            'refundable'  => $refundable,
        ];
    }
}
