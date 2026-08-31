<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EquipmentRental extends Model
{
    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'purpose',
        'status',
        'reviewed_by',
        'admin_remarks',
        'released_at',
        'returned_at',
        'claim_code',
        'payment_method',
        'payment_status',
        'payment_reference',
        'paymongo_checkout_session_id',
        'payment_channel',
        'amount_due',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'released_at' => 'datetime',
        'returned_at' => 'datetime',
        'amount_due'  => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(EquipmentRentalItem::class);
    }

    public function refundRequests(): HasMany
    {
        return $this->hasMany(RefundRequest::class);
    }

    public function latestRefundRequest(): HasOne
    {
        return $this->hasOne(RefundRequest::class)->latestOfMany();
    }

    /**
     * The resident may open a cancellation / refund request while the rental
     * is paid, not yet finished, and has no request already in flight.
     */
    public function isRefundEligible(): bool
    {
        return $this->payment_status === 'paid'
            && in_array($this->status, ['pending', 'approved', 'released'], true)
            && ! $this->refundRequests->contains(fn (RefundRequest $r) => $r->isOpen());
    }

    /**
     * Physically remove the rented quantities from barangay stock.
     * Called once, when payment is confirmed.
     */
    public function deductStock(): void
    {
        foreach ($this->items()->with('equipment')->get() as $line) {
            $line->equipment()->decrement('total_stock', $line->quantity);
        }
    }

    /**
     * Return the rented quantities to barangay stock.
     * Called when a paid rental is rejected, or its equipment is returned.
     */
    public function restoreStock(): void
    {
        foreach ($this->items()->with('equipment')->get() as $line) {
            $line->equipment()->increment('total_stock', $line->quantity);
        }
    }
}
