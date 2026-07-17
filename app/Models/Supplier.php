<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected static function booted(): void
    {
        static::created(fn (Supplier $supplier) => LedgerAccount::ensureForSupplier($supplier));
    }

    protected $fillable = [
        'name',
        'email',
        'address',
        'phone',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }
}
