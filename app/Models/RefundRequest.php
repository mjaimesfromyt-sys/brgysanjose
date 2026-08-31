<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefundRequest extends Model
{
    protected $fillable = [
        'equipment_rental_id',
        'user_id',
        'reason',
        'type',
        'status',
        'estimated_amount',
        'amount',
        'refund_method',
        'refund_reference',
        'paymongo_refund_id',
        'reviewed_by',
        'admin_remarks',
        'processed_at',
    ];

    protected $casts = [
        'estimated_amount' => 'decimal:2',
        'amount'           => 'decimal:2',
        'processed_at'     => 'datetime',
    ];

    public function rental(): BelongsTo
    {
        return $this->belongsTo(EquipmentRental::class, 'equipment_rental_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Still moving through the pipeline — a resident can't open a second one
     * while this is true.
     */
    public function isOpen(): bool
    {
        return in_array($this->status, ['requested', 'approved'], true);
    }
}
