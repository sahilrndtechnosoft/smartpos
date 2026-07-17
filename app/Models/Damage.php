<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Damage extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'code',
        'damaged_at',
        'qty',
        'cost_value',
        'reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'damaged_at' => 'datetime',
            'qty' => 'integer',
            'cost_value' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
