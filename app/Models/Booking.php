<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'user_id', 'facility_id', 'start_date', 'end_date',
        'start_time', 'end_time', 'purpose',
        'status', 'reviewed_by', 'admin_remarks',
        'claim_code', 'payment_method', 'payment_status', 'payment_reference',
        'paymongo_checkout_session_id', 'payment_channel', 'amount_due',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'amount_due' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
    public function scopeConflicting($query, $facilityId, $startDate, $endDate, $startTime, $endTime, $ignoreId = null)
    {
        return $query
            ->where('facility_id', $facilityId)
            ->whereIn('status', ['pending', 'approved'])
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId));
    }
}