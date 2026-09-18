<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaptainUnavailability extends Model
{
    protected $fillable = [
        'date', 'start_time', 'end_time', 'reason', 'created_by',
    ];

    protected $casts = [
        'date'       => 'date',
        'start_time' => 'datetime:H:i',
        'end_time'   => 'datetime:H:i',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * I-check kung ang given time range mo-overlap sa unavailability.
     */
    public static function hasConflict(string $date, string $startTime, string $endTime): bool
    {
        return static::where('date', $date)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();
    }
}
