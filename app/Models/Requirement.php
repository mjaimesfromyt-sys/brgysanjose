<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Requirement extends Model
{
    protected $fillable = ['transaction_type_id', 'item'];

    public function transactionType(): BelongsTo
    {
        return $this->belongsTo(TransactionType::class);
    }
}