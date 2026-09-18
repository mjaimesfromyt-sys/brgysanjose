<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaptainAppointment extends Model
{
    protected $fillable = [
        'user_id', 'date', 'start_time', 'end_time',
        'category', 'reason', 'status',
        'admin_remarks', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'date'        => 'date',
        'start_time'  => 'datetime:H:i',
        'end_time'    => 'datetime:H:i',
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * I-check kung ang given time range mo-overlap sa approved appointment.
     */
    public static function hasConflict(string $date, string $startTime, string $endTime, ?int $ignoreId = null): bool
    {
        return static::where('date', $date)
            ->where('status', 'approved')
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->when($ignoreId, fn ($q, $id) => $q->where('id', '!=', $id))
            ->exists();
    }
}
