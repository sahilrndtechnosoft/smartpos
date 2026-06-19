<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use SoftDeletes, HasUuids;
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $total = Inventory::withTrashed()->count();
            $code = 'GG' . date('Ymd') . 'C' . ($total + 1);
            $model->code = $code;
        });
    }
    protected $fillable = [
        'code',
        'supplier_id',
        'date',
        'status',
        'notes',
        'file'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(InventoryItem::class);
    }
}
