<?php

namespace App\Models\Shop;

use App\Models\Address;
use App\Models\Cart;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids, SoftDeletes;

    protected static function booted(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->preferences)) {
                $model->preferences = [
                    [
                        'email_notifications'   => true,
                        'sms_notifications'     => true,
                        'whatsapp_notifications' => true,
                    ],
                ];
            }
        });
        static::deleting(function (self $customer) {
            $customer->tokens()->delete();
        });
    }

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'phone_verified_at',
        'otp_code',
        'otp_expires_at',
        'is_active',
        'gender',
        'notes',
        'birthday',
        'preferences'
    ];

    /**
     * Hidden attributes
     */
    protected $hidden = [
        'otp_code',
    ];

    /**
     * Attribute casting
     */
    protected function casts(): array
    {
        return [
            'phone_verified_at' => 'datetime',
            'otp_expires_at'    => 'datetime',
            'birthday'          => 'string',
            'is_active'         => 'boolean',
            'preferences' => 'array'
        ];
    }

    /**
     * Phone verification helpers
     */
    public function hasVerifiedPhone(): bool
    {
        return ! is_null($this->phone_verified_at);
    }
    public function scopeVerified($query)
    {
        return $query->whereNotNull('phone_verified_at');
    }

    public function markPhoneAsVerified(): bool
    {
        return $this->forceFill([
            'phone_verified_at' => now(),
            'otp_code' => null,
            'otp_expires_at' => null,
        ])->save();
    }

    /**
     * Relationships
     */
    public function addresses(): BelongsToMany
    {
        return $this->belongsToMany(Address::class, 'customer_addresses');
    }
}
