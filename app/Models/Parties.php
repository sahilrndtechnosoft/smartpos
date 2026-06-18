<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parties extends Model
{
    protected $fillable = [
        'name',
        'type',
        'default_rate',
        'phone',
        'gst_number',
        'address',
        'opening_balance',
        'balance_type',
        'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
