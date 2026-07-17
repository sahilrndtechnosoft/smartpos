<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'barcode',
        'hsn_code',
        'sku',
        'description',
        'qty',
        'security_stock',
        'images',
        'featured',
        'mrp',
        'purchase_rate',
        'rate_a',
        'rate_b',
        'rate_c',
        'unit',
        'unit_value',
        'secondary_unit',
        'secondary_unit_qty',
        'product_discounts',
        'scheme_buy_qty',
        'scheme_free_qty',
        'scheme_free_product_id',
        'backorder',
        'requires_shipping',
        'published_at',
        'expired_at',
        'tags',
        'brand_id',
        'tax_group_id',
        'is_secondary',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'security_stock' => 'integer',
            'images' => 'array',
            'featured' => 'boolean',
            'mrp' => 'decimal:2',
            'purchase_rate' => 'decimal:2',
            'rate_a' => 'decimal:2',
            'rate_b' => 'decimal:2',
            'rate_c' => 'decimal:2',
            'unit_value' => 'decimal:2',
            'secondary_unit_qty' => 'decimal:2',
            'product_discounts' => 'array',
            'scheme_buy_qty' => 'integer',
            'scheme_free_qty' => 'integer',
            'backorder' => 'boolean',
            'requires_shipping' => 'boolean',
            'published_at' => 'date',
            'expired_at' => 'date',
            'is_secondary' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function taxGroup(): BelongsTo
    {
        return $this->belongsTo(TaxGroup::class);
    }

    public function schemeFreeProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'scheme_free_product_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_products');
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }
}
