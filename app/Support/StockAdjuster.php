<?php

namespace App\Support;

use App\Models\Product;

class StockAdjuster
{
    /**
     * Applies qty deltas (positive = stock in, negative = stock out) to Product.qty.
     *
     * @param  array<string, int>  $deltas  product_id => qty change
     */
    public static function apply(array $deltas): void
    {
        foreach ($deltas as $productId => $delta) {
            if ($delta === 0) {
                continue;
            }

            Product::query()->whereKey($productId)->increment('qty', $delta);
        }
    }

    /**
     * Sums qty per product_id from a list of line items (arrays or models with
     * product_id/qty attributes).
     *
     * @param  iterable<mixed>  $items
     * @return array<string, int>
     */
    public static function qtyByProduct(iterable $items): array
    {
        $totals = [];

        foreach ($items as $item) {
            $productId = is_array($item) ? ($item['product_id'] ?? null) : ($item->product_id ?? null);
            $qty = (int) (is_array($item) ? ($item['qty'] ?? 0) : ($item->qty ?? 0));

            if (blank($productId)) {
                continue;
            }

            $totals[$productId] = ($totals[$productId] ?? 0) + $qty;
        }

        return $totals;
    }

    /**
     * @param  array<string, int>  $before
     * @param  array<string, int>  $after
     * @return array<string, int> after - before, per product
     */
    public static function diff(array $before, array $after): array
    {
        $deltas = [];

        foreach (array_unique([...array_keys($before), ...array_keys($after)]) as $productId) {
            $deltas[$productId] = ($after[$productId] ?? 0) - ($before[$productId] ?? 0);
        }

        return $deltas;
    }

    /**
     * @param  array<string, int>  $deltas
     * @return array<string, int>
     */
    public static function negate(array $deltas): array
    {
        return array_map(fn (int $delta): int => -$delta, $deltas);
    }
}
