<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            TaxSeeder::class,
            TaxGroupSeeder::class,
            BrandSeeder::class,
            CategorySeeder::class,
            CategoryGroupSeeder::class,
            ProductSeeder::class,
            CustomerSeeder::class,
            SupplierSeeder::class,
            OrderSeeder::class,
            InventorySeeder::class,
            SettingSeeder::class,
            SessionSeeder::class,
            NotificationSeeder::class,
            KeyboardShortcutSeeder::class,
        ]);
    }
}
