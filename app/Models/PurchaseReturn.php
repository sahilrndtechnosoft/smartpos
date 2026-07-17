<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReturn extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'inventory_id',
        'supplier_id',
        'code',
        'returned_at',
        'refund_mode',
        'reason',
        'total',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'returned_at' => 'datetime',
            'total' => 'decimal:2',
        ];
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function recalculateTotal(): void
    {
        $this->load('items');

        $this->update([
            'total' => $this->items->sum('amount'),
        ]);
    }
}
