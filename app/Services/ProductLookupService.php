<?php

namespace App\Services;

use App\Models\Product;

class ProductLookupService
{
    public function findByBarcodeOrSku(string $code): ?Product
    {
        $code = trim($code);

        if ($code === '') {
            return null;
        }

        return Product::query()
            ->where('is_active', true)
            ->where(function ($query) use ($code): void {
                $query->where('barcode', $code)
                    ->orWhere('sku', $code);
            })
            ->first();
    }
}
