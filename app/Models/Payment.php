<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'payable_type',
        'payable_id',
        'code',
        'type',
        'method',
        'tax_amount',
        'amount',
        'status',
        'meta',
        'payment_at',
    ];

    protected function casts(): array
    {
        return [
            'tax_amount' => 'decimal:2',
            'amount' => 'decimal:2',
            'meta' => 'array',
            'payment_at' => 'datetime',
        ];
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }
}
