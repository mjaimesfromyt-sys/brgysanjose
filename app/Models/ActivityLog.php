<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'user_name',
        'role',
        'action',
        'control_number',
        'filters',
        'ip_address',
    ];

    protected $casts = [
        'filters' => 'array',
    ];
}