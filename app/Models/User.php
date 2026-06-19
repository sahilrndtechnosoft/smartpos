<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\ModuleRegistry;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable,HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_super_admin',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }

    public function modulePermissions(): HasMany
    {
        return $this->hasMany(UserModulePermission::class);
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    public function syncModulePermissions(): void
    {
        foreach (ModuleRegistry::keys() as $module) {
            $this->modulePermissions()->firstOrCreate(
                ['module' => $module],
                [
                    'can_view' => false,
                    'can_create' => false,
                    'can_edit' => false,
                    'can_delete' => false,
                ],
            );
        }
    }

    public function hasModulePermission(string $module, string $ability): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $permission = $this->modulePermissions
            ->firstWhere('module', $module)
            ?? $this->modulePermissions()->where('module', $module)->first();

        if (! $permission) {
            return false;
        }

        return match ($ability) {
            'view', 'viewAny' => $permission->can_view,
            'create' => $permission->can_create,
            'edit', 'update' => $permission->can_edit,
            'delete' => $permission->can_delete,
            default => false,
        };
    }

    /**
     * @param  array<string, array<string, bool>>  $permissions
     */
    public function applyModulePermissions(array $permissions): void
    {
        $this->syncModulePermissions();

        foreach (ModuleRegistry::keys() as $module) {
            $modulePermissions = $permissions[$module] ?? [];

            $this->modulePermissions()->updateOrCreate(
                ['module' => $module],
                [
                    'can_view' => (bool) ($modulePermissions['can_view'] ?? false),
                    'can_create' => (bool) ($modulePermissions['can_create'] ?? false),
                    'can_edit' => (bool) ($modulePermissions['can_edit'] ?? false),
                    'can_delete' => (bool) ($modulePermissions['can_delete'] ?? false),
                ],
            );
        }
    }
}
