<?php

namespace App\Filament\Resources\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait AuthorizesModuleAccess
{
    abstract protected static function getModuleKey(): string;

    protected static function authorizeModule(string $ability): bool
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->hasModulePermission(static::getModuleKey(), $ability);
    }

    public static function canViewAny(): bool
    {
        return static::authorizeModule('viewAny');
    }

    public static function canView(Model $record): bool
    {
        return static::authorizeModule('view');
    }

    public static function canCreate(): bool
    {
        return static::authorizeModule('create');
    }

    public static function canEdit(Model $record): bool
    {
        return static::authorizeModule('edit');
    }

    public static function canDelete(Model $record): bool
    {
        return static::authorizeModule('delete');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }
}
