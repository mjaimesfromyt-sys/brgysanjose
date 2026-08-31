<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentRentalItem extends Model
{
    protected $fillable = ['equipment_rental_id', 'equipment_id', 'quantity'];

    public function rental(): BelongsTo
    {
        return $this->belongsTo(EquipmentRental::class, 'equipment_rental_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }
}
