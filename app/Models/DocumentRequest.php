<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentRequest extends Model
{
    protected $fillable = [
         'user_id', 'transaction_type_id', 'claim_code', 'purpose', 'business_address',
    'status', 'admin_remarks', 'reviewed_by', 'validated_at', 'claimed_at',
    'payment_method', 'payment_status', 'payment_reference',
    'paymongo_checkout_session_id', 'payment_channel', 'amount_due', 'occupation', 'monthly_income', 'employed_since',
    'verification_code',
    'control_number',
    'issued_at',
    // Certificate-specific fields
    'lot_no', 'tax_dec_no', 'cad_no', 'land_area',
    'land_owner', 'owner_spouse', 'tenant_name',
    'requester_name', 'subject_name',
    'deceased_name', 'date_died', 'burial_place',
    'control_no', 'disability_type', 'solo_parent_since',
    'on_duty_name', 'on_duty_label',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
        'claimed_at'   => 'datetime',
        'amount_due'   => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactionType(): BelongsTo
    {
        return $this->belongsTo(TransactionType::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}