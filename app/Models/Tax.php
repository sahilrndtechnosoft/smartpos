<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tax extends Model
{
    protected $fillable = ['name', 'rate', 'is_active', 'type'];
    protected $hidden = ['pivot'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function taxGroup(): BelongsToMany
    {
        return $this->belongsToMany(TaxGroup::class, 'tax_group_items');
    }
}
