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

    public const CASH_CUSTOMER_PHONE = '0000000000';

    protected static function booted(): void
    {
        static::created(fn (Customer $customer) => LedgerAccount::ensureForCustomer($customer));
    }

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

    /**
     * The default walk-in party used to preselect sale bills.
     */
    public static function cashCustomer(): self
    {
        return self::query()->firstOrCreate(
            ['phone' => self::CASH_CUSTOMER_PHONE],
            [
                'name' => 'Cash',
                'is_active' => true,
                'preferences' => [],
            ],
        );
    }
}
