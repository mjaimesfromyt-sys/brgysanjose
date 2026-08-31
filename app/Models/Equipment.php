<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    protected $table = 'equipment';

    protected $fillable = ['name', 'description', 'fee', 'total_stock', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'fee'       => 'decimal:2',
    ];

    public function rentalItems(): HasMany
    {
        return $this->hasMany(EquipmentRentalItem::class);
    }

    /**
     * Units already reserved or out for overlapping dates.
     *
     * Paid rentals are excluded here because their quantity has already been
     * physically subtracted from total_stock (see EquipmentRental::deductStock()) —
     * counting them again here would subtract them twice.
     */
    public function committedQuantity(string $startDate, string $endDate, ?int $ignoreRentalId = null): int
    {
        return (int) EquipmentRentalItem::query()
            ->where('equipment_id', $this->id)
            ->whereHas('rental', function ($query) use ($startDate, $endDate, $ignoreRentalId) {
                $query->whereIn('status', ['pending', 'approved', 'released'])
                    ->where('payment_status', '!=', 'paid')
                    ->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate)
                    ->when($ignoreRentalId, fn ($q) => $q->where('id', '!=', $ignoreRentalId));
            })
            ->sum('quantity');
    }

    public function availableFor(string $startDate, string $endDate, ?int $ignoreRentalId = null): int
    {
        return max(0, $this->total_stock - $this->committedQuantity($startDate, $endDate, $ignoreRentalId));
    }
}
