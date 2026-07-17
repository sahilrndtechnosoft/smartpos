<?php

namespace App\Support;

use App\Models\Product;

class ProductRateOptions
{
    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            'mrp' => 'MRP',
            'rate_a' => 'Rate A',
            'rate_b' => 'Rate B',
            'rate_c' => 'Rate C',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function forProduct(?Product $product): array
    {
        if (! $product) {
            return self::labels();
        }

        return collect(self::labels())
            ->mapWithKeys(fn (string $label, string $key): array => [
                $key => sprintf('%s (₹%s)', $label, number_format((float) $product->{$key}, 2)),
            ])
            ->all();
    }

    public static function priceFor(Product $product, string $rateKey, int $qty = 1): float
    {
        $slabPrice = self::slabPriceFor($product, $rateKey, $qty);

        if ($slabPrice !== null) {
            return $slabPrice;
        }

        return (float) match ($rateKey) {
            'mrp' => $product->mrp,
            'rate_b' => $product->rate_b,
            'rate_c' => $product->rate_c,
            default => $product->rate_a,
        };
    }

    /**
     * Picks the best matching qty-wise rate slab (highest qty threshold that the
     * given qty still satisfies) for the rate type, or null if none applies.
     */
    protected static function slabPriceFor(Product $product, string $rateKey, int $qty): ?float
    {
        $slabs = $product->product_discounts;

        if (! is_array($slabs)) {
            return null;
        }

        $bestMatch = collect($slabs)
            ->filter(fn ($slab): bool => is_array($slab)
                && ($slab['rate_type'] ?? null) === $rateKey
                && (int) ($slab['qty'] ?? 0) <= $qty)
            ->sortByDesc(fn (array $slab): int => (int) $slab['qty'])
            ->first();

        return $bestMatch === null ? null : (float) $bestMatch['price'];
    }

    public static function label(string $rateKey): string
    {
        return self::labels()[$rateKey] ?? ucfirst(str_replace('_', ' ', $rateKey));
    }

    /**
     * @param  array<string, mixed>|null  $snapshot
     */
    public static function detectFromSnapshot(?array $snapshot, float $unitPrice): string
    {
        $applied = $snapshot['applied_rate'] ?? null;

        if (filled($applied)) {
            return (string) $applied;
        }

        foreach (array_keys(self::labels()) as $key) {
            if (isset($snapshot[$key]) && (float) $snapshot[$key] === $unitPrice) {
                return $key;
            }
        }

        return 'rate_a';
    }
}
