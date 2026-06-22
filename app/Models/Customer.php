<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'phone',
        'name',
        'email',
        'gender',
        'birthday',
        'is_active',
        'phone_verified_at',
        'otp_code',
        'otp_expires_at',
        'preferences',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
            'is_active' => 'boolean',
            'phone_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'preferences' => 'array',
        ];
    }

    public function addresses(): BelongsToMany
    {
        return $this->belongsToMany(Address::class, 'customer_addresses');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
