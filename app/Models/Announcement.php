<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = [
        'title', 'body', 'category',
        'is_published', 'is_pinned', 'published_at', 'created_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_pinned'    => 'boolean',
        'published_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Only announcements the public should see: published, and not
     * scheduled for a future date.
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->where(function (Builder $q) {
                $q->whereNull('published_at')
                  ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * Pinned items first, then most recent.
     */
    public function scopeNewestFirst(Builder $query): Builder
    {
        return $query
            ->orderByDesc('is_pinned')
            ->orderByRaw('COALESCE(published_at, created_at) DESC');
    }

    /**
     * Date to show the public — falls back to when the record was created.
     */
    public function getDisplayDateAttribute()
    {
        return $this->published_at ?? $this->created_at;
    }
}
