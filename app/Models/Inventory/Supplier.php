<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes, HasUuids;

    protected $fillable = ['name', 'email', 'phone', 'address','notes','is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }
}
