<?php

namespace App\Models\Shop;

use App\Models\CartItem;
use App\Models\Inventory\InventoryItem;
use App\Models\Tax;
use App\Models\TaxGroup;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasUuids, SoftDeletes, HasFactory;
    protected $hidden = ['pivot'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $code = 'P' . date('YmdW') . random_int(1000, 9999);
            $model->barcode = $code;
            $model->sku = $code;
            $model->slug = hcGetSlug($model->name);
        });
    }

    protected $fillable = [
        'name',
        'slug',
        'barcode',
        'description',
        'qty',
        'security_stock',
        'featured',
        'is_visible',
        'old_price',
        'price',
        'cost',
        'backorder',
        'published_at',
        'images',
        'brand_id',
        'unit',
        'tags',
        'sku',
        'unit_value',
        'product_discounts',
        'tax_group_id',
        'expired_at'
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'security_stock' => 'integer',
            'featured' => 'boolean',
            'is_visible' => 'boolean',
            'backorder' => 'boolean',
            'images' => 'array',
            'tags' => 'array',
            'product_discounts' => 'array',
            'expired_at' => 'date',
        ];
    }
    protected static function booted()
    {
        static::addGlobalScope('visible', function (Builder $builder) {
            $builder
                ->where('is_visible', true)
                ->where('published_at', '<=', now());
        });
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_products');
    }
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function inventorytems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function taxGroup(): BelongsTo
    {
        return $this->belongsTo(TaxGroup::class);
    }
}
