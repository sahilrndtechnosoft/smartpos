<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()->where('email', 'admin@smartpos.local')->value('id');

        $products = [
            [
                'name' => 'Basmati Rice 5kg',
                'slug' => 'basmati-rice-5kg',
                'description' => 'Premium long-grain basmati rice.',
                'category' => 'Groceries',
                'sub_category' => 'Rice & Pulses',
                'unit' => 'PKT',
                'pieces_per_box' => 1,
                'hsn_code' => '1006',
                'tax_rate' => 5,
                'track_expiry' => false,
                'reorder_level' => 10,
                'reorder_qty' => 50,
                'barcode' => '8901001001001',
                'is_secondary' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Sunflower Oil 1L',
                'slug' => 'sunflower-oil-1l',
                'description' => 'Refined cooking oil.',
                'category' => 'Groceries',
                'sub_category' => 'Oils',
                'unit' => 'LTR',
                'pieces_per_box' => 12,
                'hsn_code' => '1512',
                'tax_rate' => 5,
                'track_expiry' => true,
                'reorder_level' => 20,
                'reorder_qty' => 100,
                'barcode' => '8901001001002',
                'is_secondary' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Full Cream Milk 1L',
                'slug' => 'full-cream-milk-1l',
                'description' => 'Fresh pasteurized milk.',
                'category' => 'Dairy',
                'sub_category' => 'Milk',
                'unit' => 'LTR',
                'pieces_per_box' => 6,
                'hsn_code' => '0401',
                'tax_rate' => 0,
                'track_expiry' => true,
                'reorder_level' => 30,
                'reorder_qty' => 120,
                'barcode' => '8901001001003',
                'is_secondary' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Mineral Water 1L',
                'slug' => 'mineral-water-1l',
                'description' => 'Packaged drinking water.',
                'category' => 'Beverages',
                'sub_category' => 'Water',
                'unit' => 'PCS',
                'pieces_per_box' => 24,
                'hsn_code' => '2201',
                'tax_rate' => 12,
                'track_expiry' => true,
                'reorder_level' => 50,
                'reorder_qty' => 200,
                'barcode' => '8901001001004',
                'is_secondary' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Potato Chips 50g',
                'slug' => 'potato-chips-50g',
                'description' => 'Classic salted potato chips.',
                'category' => 'Snacks',
                'sub_category' => 'Chips',
                'unit' => 'PCS',
                'pieces_per_box' => 48,
                'hsn_code' => '2106',
                'tax_rate' => 12,
                'track_expiry' => true,
                'reorder_level' => 40,
                'reorder_qty' => 150,
                'barcode' => '8901001001005',
                'is_secondary' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Detergent Powder 1kg',
                'slug' => 'detergent-powder-1kg',
                'description' => 'Laundry detergent for daily use.',
                'category' => 'Household',
                'sub_category' => 'Cleaning',
                'unit' => 'PKT',
                'pieces_per_box' => 10,
                'hsn_code' => '3402',
                'tax_rate' => 18,
                'track_expiry' => false,
                'reorder_level' => 15,
                'reorder_qty' => 60,
                'barcode' => '8901001001006',
                'is_secondary' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Cola Soft Drink 750ml',
                'slug' => 'cola-soft-drink-750ml',
                'description' => 'Carbonated soft drink.',
                'category' => 'Beverages',
                'sub_category' => 'Soft Drinks',
                'unit' => 'PCS',
                'pieces_per_box' => 24,
                'hsn_code' => '2202',
                'tax_rate' => 12,
                'track_expiry' => true,
                'reorder_level' => 60,
                'reorder_qty' => 240,
                'barcode' => '8901001001007',
                'is_secondary' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Wheat Flour 10kg',
                'slug' => 'wheat-flour-10kg',
                'description' => 'Whole wheat atta for daily cooking.',
                'category' => 'Groceries',
                'sub_category' => 'Flour',
                'unit' => 'PKT',
                'pieces_per_box' => 1,
                'hsn_code' => '1101',
                'tax_rate' => 5,
                'track_expiry' => false,
                'reorder_level' => 8,
                'reorder_qty' => 40,
                'barcode' => '8901001001008',
                'is_secondary' => false,
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::query()->updateOrCreate(
                ['slug' => $product['slug']],
                [
                    ...$product,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                ],
            );
        }
    }
}
