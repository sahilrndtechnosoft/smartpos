<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\BatchItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class BatchSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()->where('email', 'admin@smartpos.local')->value('id');

        $batch = Batch::query()->updateOrCreate(
            ['batch_number' => 'BATCH-2026-001'],
            [
                'created_by' => $adminId,
                'updated_by' => $adminId,
            ],
        );

        $items = [
            'basmati-rice-5kg' => [
                'barcode' => '8902002002001',
                'expiry_date' => null,
                'mrp' => 599,
                'purchase_rate' => 450,
                'rate_a' => 520,
                'rate_b' => 510,
                'rate_c' => 500,
                'stock_primary' => 100,
                'stock_secondary' => 0,
            ],
            'sunflower-oil-1l' => [
                'barcode' => '8902002002002',
                'expiry_date' => now()->addMonths(8)->toDateString(),
                'mrp' => 165,
                'purchase_rate' => 120,
                'rate_a' => 145,
                'rate_b' => 140,
                'rate_c' => 135,
                'stock_primary' => 240,
                'stock_secondary' => 0,
            ],
            'full-cream-milk-1l' => [
                'barcode' => '8902002002003',
                'expiry_date' => now()->addDays(7)->toDateString(),
                'mrp' => 68,
                'purchase_rate' => 52,
                'rate_a' => 62,
                'rate_b' => 60,
                'rate_c' => 58,
                'stock_primary' => 72,
                'stock_secondary' => 0,
            ],
            'mineral-water-1l' => [
                'barcode' => '8902002002004',
                'expiry_date' => now()->addMonths(12)->toDateString(),
                'mrp' => 20,
                'purchase_rate' => 12,
                'rate_a' => 18,
                'rate_b' => 17,
                'rate_c' => 16,
                'stock_primary' => 480,
                'stock_secondary' => 0,
            ],
            'potato-chips-50g' => [
                'barcode' => '8902002002005',
                'expiry_date' => now()->addMonths(4)->toDateString(),
                'mrp' => 25,
                'purchase_rate' => 14,
                'rate_a' => 20,
                'rate_b' => 19,
                'rate_c' => 18,
                'stock_primary' => 200,
                'stock_secondary' => 0,
            ],
            'detergent-powder-1kg' => [
                'barcode' => '8902002002006',
                'expiry_date' => null,
                'mrp' => 220,
                'purchase_rate' => 160,
                'rate_a' => 195,
                'rate_b' => 190,
                'rate_c' => 185,
                'stock_primary' => 80,
                'stock_secondary' => 0,
            ],
            'cola-soft-drink-750ml' => [
                'barcode' => '8902002002007',
                'expiry_date' => now()->addMonths(6)->toDateString(),
                'mrp' => 40,
                'purchase_rate' => 28,
                'rate_a' => 36,
                'rate_b' => 35,
                'rate_c' => 34,
                'stock_primary' => 360,
                'stock_secondary' => 0,
            ],
            'wheat-flour-10kg' => [
                'barcode' => '8902002002008',
                'expiry_date' => null,
                'mrp' => 450,
                'purchase_rate' => 340,
                'rate_a' => 400,
                'rate_b' => 395,
                'rate_c' => 390,
                'stock_primary' => 60,
                'stock_secondary' => 0,
            ],
        ];

        $products = Product::query()
            ->whereIn('slug', array_keys($items))
            ->pluck('id', 'slug');

        foreach ($items as $slug => $item) {
            $productId = $products[$slug] ?? null;

            if (! $productId) {
                continue;
            }

            BatchItem::query()->updateOrCreate(
                [
                    'batch_id' => $batch->id,
                    'product_id' => $productId,
                ],
                [
                    ...$item,
                    'is_secondary' => false,
                    'is_active' => true,
                ],
            );
        }

        $secondBatch = Batch::query()->updateOrCreate(
            ['batch_number' => 'BATCH-2026-002'],
            [
                'created_by' => $adminId,
                'updated_by' => $adminId,
            ],
        );

        $restockItems = [
            'basmati-rice-5kg' => [
                'barcode' => '8902002003001',
                'expiry_date' => null,
                'mrp' => 609,
                'purchase_rate' => 460,
                'rate_a' => 525,
                'rate_b' => 515,
                'rate_c' => 505,
                'stock_primary' => 50,
                'stock_secondary' => 0,
            ],
            'sunflower-oil-1l' => [
                'barcode' => '8902002003002',
                'expiry_date' => now()->addMonths(10)->toDateString(),
                'mrp' => 170,
                'purchase_rate' => 125,
                'rate_a' => 148,
                'rate_b' => 143,
                'rate_c' => 138,
                'stock_primary' => 120,
                'stock_secondary' => 0,
            ],
        ];

        foreach ($restockItems as $slug => $item) {
            $productId = $products[$slug] ?? null;

            if (! $productId) {
                continue;
            }

            BatchItem::query()->updateOrCreate(
                [
                    'batch_id' => $secondBatch->id,
                    'product_id' => $productId,
                ],
                [
                    ...$item,
                    'is_secondary' => false,
                    'is_active' => true,
                ],
            );
        }
    }
}
