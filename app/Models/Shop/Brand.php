<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use HasUuids, SoftDeletes, HasFactory;

    protected $fillable = [
        'name',
        'image',
        'website',
        'slug',
        'featured',
        'description',
        'is_visible',
        'sr'
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'sr' => 'integer',
            'featured' => 'boolean',
        ];
    }
    
    protected static function booted(): void
    {
        static::addGlobalScope('visible', function (Builder $builder) {
            $builder->where('is_visible', true);
        });
        
        static::deleting(function (self $brand) {
            $brand->products()->delete();
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
