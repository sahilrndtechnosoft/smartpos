<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'sr',
        'inventory_id',
        'product_id',
        'batch_no',
        'qty',
        'mrp',
        'purchase_rate',
        'rate_a',
        'rate_b',
        'rate_c',
        'tax_total',
        'expiry_date',
        'is_locked',
        'is_secondary',
    ];

    protected function casts(): array
    {
        return [
            'sr' => 'integer',
            'qty' => 'integer',
            'mrp' => 'decimal:2',
            'purchase_rate' => 'decimal:2',
            'rate_a' => 'decimal:2',
            'rate_b' => 'decimal:2',
            'rate_c' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'expiry_date' => 'date',
            'is_locked' => 'boolean',
            'is_secondary' => 'boolean',
        ];
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
