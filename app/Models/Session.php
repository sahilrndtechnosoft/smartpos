<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Session extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'sessions';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'last_activity' => 'integer',
        'user_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getLastActivityAtAttribute(): ?Carbon
    {
        if (! $this->last_activity) {
            return null;
        }

        return Carbon::createFromTimestamp($this->last_activity);
    }

    public function isCurrent(): bool
    {
        return $this->id === session()->getId();
    }

    public function isActive(): bool
    {
        if (! $this->last_activity) {
            return false;
        }

        $lifetime = (int) config('session.lifetime', 120) * 60;

        return (time() - $this->last_activity) < $lifetime;
    }

    public function getStatusLabel(): string
    {
        if ($this->isCurrent()) {
            return 'Current';
        }

        if ($this->isActive()) {
            return 'Active';
        }

        return 'Expired';
    }
}
