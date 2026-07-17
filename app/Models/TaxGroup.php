<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxGroup extends Model
{
    protected $fillable = [
        'name',
        'notes',
        'hsn',
    ];

    public function taxes(): BelongsToMany
    {
        return $this->belongsToMany(Tax::class, 'tax_group_items');
    }

    /**
     * The combined tax rate for this group (e.g. CGST + SGST), as a percentage.
     */
    public function effectiveRate(): float
    {
        return (float) $this->taxes->sum('rate');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
