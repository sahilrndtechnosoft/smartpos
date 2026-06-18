<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserModulePermission;
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

        UserModulePermission::query()->updateOrCreate(
            [
                'user_id' => $staff->id,
                'module' => 'products',
            ],
            [
                'can_view' => true,
                'can_create' => true,
                'can_edit' => true,
                'can_delete' => false,
            ],
        );

        UserModulePermission::query()->updateOrCreate(
            [
                'user_id' => $staff->id,
                'module' => 'sessions',
            ],
            [
                'can_view' => true,
                'can_create' => false,
                'can_edit' => false,
                'can_delete' => false,
            ],
        );
    }
}
