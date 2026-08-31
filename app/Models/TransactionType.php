<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransactionType extends Model
{
    protected $fillable = ['name', 'description', 'requires_residency', 'fee', 'is_active'];

    protected $casts = [
        'requires_residency' => 'boolean',
        'is_active'          => 'boolean',
        'fee'                => 'decimal:2',
    ];

    public function requirements(): HasMany
    {
        return $this->hasMany(Requirement::class);
    }
}