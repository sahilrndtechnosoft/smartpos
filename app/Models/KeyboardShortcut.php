<?php

namespace App\Models;

use App\Support\KeyboardShortcutCombination;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KeyboardShortcut extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'combination',
        'action_type',
        'action_target',
        'action_record_id',
        'description',
        'is_active',
        'sr',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sr' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (KeyboardShortcut $shortcut): void {
            $shortcut->combination = KeyboardShortcutCombination::normalize($shortcut->combination);
        });
    }
}
