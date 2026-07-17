<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'supplier_id',
        'status',
        'date',
        'tax_inclusive',
        'notes',
        'file',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'tax_inclusive' => 'boolean',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function purchaseReturns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }
}
