<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReturnItem extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'purchase_return_id',
        'inventory_item_id',
        'product_id',
        'product_name',
        'qty',
        'purchase_rate',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'purchase_rate' => 'decimal:2',
            'amount' => 'decimal:2',
        ];
    }

    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
