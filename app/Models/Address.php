<?php

namespace App\Models;

use App\Models\Shop\Customer;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasFactory, SoftDeletes, HasUuids;
    protected $hidden = ['pivot'];

    protected $fillable = [
        'title',
        'receiver_name',
        'receiver_phone',
        'street',
        'city',
        'state',
        'country',
        'latitude',
        'longitude',
        'description',
        'meta',
    ];
    protected function casts(): array
    {
        return [
            'street' => 'array',
            'meta' => 'array',
        ];
    }
    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_addresses');
    }
    
}
