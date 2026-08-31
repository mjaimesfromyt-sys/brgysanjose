<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    protected $fillable = [
        'title', 'description', 'start_date', 'end_date',
        'start_time', 'end_time', 'facility_id', 'blocks_facility', 'created_by',
    ];

    protected $casts = [
        'start_date'      => 'date',
        'end_date'        => 'date',
        'blocks_facility' => 'boolean',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Events that occupy a given date range with a daily time window on a facility
    public function scopeBlockingConflict($query, $facilityId, $startDate, $endDate, $startTime, $endTime)
    {
        return $query
            ->where('blocks_facility', true)
            ->where('facility_id', $facilityId)
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime);
    }
    // Other blocking events that overlap the given facility, date range, and daily time window
    public function scopeOverlappingBlockingEvent($query, $facilityId, $startDate, $endDate, $startTime, $endTime, $ignoreId = null)
    {
        return $query
            ->where('blocks_facility', true)
            ->where('facility_id', $facilityId)
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId));
    }
}