<?php

namespace App\Models;

use App\Models\Shop\Product;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxGroup extends Model
{
    protected $fillable = ['name', 'notes','hsn'];
    protected $hidden = ['pivot'];

    public function taxes(): BelongsToMany
    {
        return $this->belongsToMany(Tax::class, 'tax_group_items', 'tax_group_id', 'tax_id');    
    }

    public function products(){
        return $this->hasMany(Product::class);
    }
}
