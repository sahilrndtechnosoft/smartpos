<?php

namespace App\Models\Inventory;

use ApiResponse;
use App\Models\Shop\Product;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use SoftDeletes, HasUuids;

    protected $fillable = [
        'inventory_id',
        'product_id',
        'sr',
        'qty',
        'price',
        'cost_price',
        'old_price',
        'expiry_date',
        'is_locked'
    ];

    protected $casts = ['is_locked' => 'boolean'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($item) {
            $maxSr = InventoryItem::where('inventory_id', $item->inventory_id)->max('sr');
            $item->sr = $maxSr ? $maxSr + 1 : 1;
        });
        static::updating(function ($model) {
            if ($model->getOriginal('is_locked') == true) {
                return false;
            }
        });
        static::deleting(function ($model) {
            if ($model->getOriginal('is_locked') == true) {
                return false;
            }
        });
    }
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
