<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customer = Customer::query()->where('phone', '9724806960')->first();

        if (! $customer) {
            return;
        }

        $lines = [
            ['sku' => 'AMUL-MILK-1L', 'qty' => 2],
            ['sku' => 'BRIT-GOODDAY-600', 'qty' => 1],
        ];

        $order = Order::query()->updateOrCreate(
            ['code' => 'SO-DEMO-001'],
            [
                'customer_id' => $customer->id,
                'ordered_at' => now()->subDays(2),
                'payment_mode' => 'cod',
                'notes' => 'Demo sales order for Aniket.',
            ],
        );

        foreach ($lines as $line) {
            $product = Product::query()->where('sku', $line['sku'])->first();

            if (! $product) {
                continue;
            }

            $subtotal = round($line['qty'] * $product->rate_a, 2);

            OrderItem::query()->updateOrCreate(
                [
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                ],
                [
                    'product_name' => $product->name,
                    'qty' => $line['qty'],
                    'unit_price' => $product->rate_a,
                    'subtotal' => $subtotal,
                    'discount_type' => null,
                    'discount_value' => null,
                    'discount_amount' => 0,
                    'tax_total' => 0,
                    'final_price' => $subtotal,
                    'primary_total' => $product->is_secondary ? null : $subtotal,
                    'secondary_total' => $product->is_secondary ? $subtotal : null,
                    'product_snapshot' => [
                        'sku' => $product->sku,
                        'mrp' => $product->mrp,
                        'rate_a' => $product->rate_a,
                        'rate_b' => $product->rate_b,
                        'rate_c' => $product->rate_c,
                        'applied_rate' => 'rate_a',
                        'is_secondary' => $product->is_secondary,
                    ],
                ],
            );
        }

        $order->recalculateTotals();
    }
}
