<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasUuids, SoftDeletes, HasFactory;

    protected $fillable = [
        'sr',
        'name',
        'slug',
        'featured',
        'is_visible',
        'image',
        'description',
    ];

    protected $hidden = ['pivot'];

    protected static function booted(): void
    {
        static::deleting(function (self $categoryGroup) {
            $categoryGroup->categoryGroupItem()->delete();
            $categoryGroup->productsItems()->delete();
        });
        
        static::addGlobalScope('visible', function (Builder $builder) {
            $builder->where('is_visible', true);
        });
    }

    protected function casts(): array
    {
        return [
            'sr' => 'integer',
            'featured' => 'boolean',
            'is_visible' => 'boolean',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'category_products');
    }

    public function productsItems(): HasMany
    {
        return $this->hasMany(CategoryProduct::class);
    }

}
