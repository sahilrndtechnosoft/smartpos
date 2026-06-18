<?php

namespace Database\Seeders;

use App\Models\Parties;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PartySeeder extends Seeder
{
    public function run(): void
    {
        $parties = [
            [
                'name' => 'Sharma General Store',
                'type' => 'customer',
                'default_rate' => 'A',
                'phone' => '9876543210',
                'gst_number' => '27AABCS1234A1Z5',
                'address' => '12 Market Road, Pune, Maharashtra',
                'opening_balance' => 5000,
                'balance_type' => 'debit',
                'is_active' => true,
            ],
            [
                'name' => 'Patel Wholesale Mart',
                'type' => 'customer',
                'default_rate' => 'B',
                'phone' => '9876543211',
                'gst_number' => '24AABCP5678B1Z2',
                'address' => '45 Ring Road, Ahmedabad, Gujarat',
                'opening_balance' => 12000,
                'balance_type' => 'debit',
                'is_active' => true,
            ],
            [
                'name' => 'Gupta Traders',
                'type' => 'supplier',
                'default_rate' => 'A',
                'phone' => '9876543212',
                'gst_number' => '07AABCG9012C1Z8',
                'address' => '78 Sadar Bazaar, Delhi',
                'opening_balance' => 25000,
                'balance_type' => 'credit',
                'is_active' => true,
            ],
            [
                'name' => 'National Foods Distributors',
                'type' => 'supplier',
                'default_rate' => 'A',
                'phone' => '9876543213',
                'gst_number' => '19AABCN3456D1Z1',
                'address' => 'Industrial Area, Kolkata, West Bengal',
                'opening_balance' => 45000,
                'balance_type' => 'credit',
                'is_active' => true,
            ],
            [
                'name' => 'City Retail Hub',
                'type' => 'both',
                'default_rate' => 'C',
                'phone' => '9876543214',
                'gst_number' => '29AABCR7890E1Z3',
                'address' => 'MG Road, Bengaluru, Karnataka',
                'opening_balance' => 8000,
                'balance_type' => 'debit',
                'is_active' => true,
            ],
        ];

        $partyIds = [];

        foreach ($parties as $party) {
            $record = Parties::query()->updateOrCreate(
                ['name' => $party['name']],
                $party,
            );

            $partyIds[$party['name']] = $record->id;
        }

        $productRates = [
            'basmati-rice-5kg' => ['A' => 520, 'B' => 510, 'C' => 500],
            'sunflower-oil-1l' => ['A' => 145, 'B' => 140, 'C' => 135],
            'full-cream-milk-1l' => ['A' => 62, 'B' => 60, 'C' => 58],
            'mineral-water-1l' => ['A' => 18, 'B' => 17, 'C' => 16],
            'potato-chips-50g' => ['A' => 20, 'B' => 19, 'C' => 18],
        ];

        $products = Product::query()
            ->whereIn('slug', array_keys($productRates))
            ->pluck('id', 'slug');

        $rateRows = [];

        foreach ($productRates as $slug => $rates) {
            $productId = $products[$slug] ?? null;

            if (! $productId) {
                continue;
            }

            foreach ($partyIds as $partyName => $partyId) {
                $party = collect($parties)->firstWhere('name', $partyName);
                $rate = $rates[$party['default_rate']] ?? $rates['A'];

                $rateRows[] = [
                    'party_id' => $partyId,
                    'product_id' => $productId,
                    'rate' => $rate,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('party_item_rates')->upsert(
            $rateRows,
            ['party_id', 'product_id'],
            ['rate', 'updated_at'],
        );
    }
}
