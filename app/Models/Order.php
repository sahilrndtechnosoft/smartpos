<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'code',
        'ordered_at',
        'payment_mode',
        'total',
        'discount_total',
        'grand_total',
        'secondary_total',
        'primary_total',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'ordered_at' => 'datetime',
            'total' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'secondary_total' => 'decimal:2',
            'primary_total' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function recalculateTotals(): void
    {
        $this->load('items');

        $this->update([
            'total' => $this->items->sum('subtotal'),
            'discount_total' => $this->items->sum('discount_amount'),
            'grand_total' => $this->items->sum('final_price'),
            'primary_total' => $this->items->sum('primary_total'),
            'secondary_total' => $this->items->sum('secondary_total'),
        ]);
    }
}
