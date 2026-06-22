<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserModulePermission;
use App\Support\ModuleRegistry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@smartpos.local'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_super_admin' => true,
            ],
        );

        $staff = User::query()->updateOrCreate(
            ['email' => 'staff@smartpos.local'],
            [
                'name' => 'Store Staff',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_super_admin' => false,
            ],
        );

        $staff->syncModulePermissions();

        $staffModules = [
            'products' => ['can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => false],
            'categories' => ['can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => false],
            'category_groups' => ['can_view' => true, 'can_create' => false, 'can_edit' => false, 'can_delete' => false],
            'brands' => ['can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => false],
            'inventories' => ['can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => false],
            'customers' => ['can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => false],
            'suppliers' => ['can_view' => true, 'can_create' => false, 'can_edit' => false, 'can_delete' => false],
            'orders' => ['can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => false],
            'keyboard_shortcuts' => ['can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => false],
            'taxes' => ['can_view' => true, 'can_create' => false, 'can_edit' => false, 'can_delete' => false],
            'tax_groups' => ['can_view' => true, 'can_create' => false, 'can_edit' => false, 'can_delete' => false],
            'settings' => ['can_view' => true, 'can_create' => false, 'can_edit' => false, 'can_delete' => false],
            'sessions' => ['can_view' => true, 'can_create' => false, 'can_edit' => false, 'can_delete' => false],
        ];

        foreach ($staffModules as $module => $permissions) {
            if (! in_array($module, ModuleRegistry::keys(), true)) {
                continue;
            }

            UserModulePermission::query()->updateOrCreate(
                ['user_id' => $staff->id, 'module' => $module],
                $permissions,
            );
        }
    }
}
