<?php

namespace App\Services;

use App\Filament\Resources\Inventories\Schemas\InventoryForm;
use App\Models\Product;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class InventoryItemsCsvParser
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function parse(string $path): array
    {
        if (! is_readable($path)) {
            throw new InvalidArgumentException('The CSV file could not be read.');
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new InvalidArgumentException('The CSV file could not be opened.');
        }

        $items = [];
        $headers = null;
        $lineNumber = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if ($row === [null] || $row === false) {
                continue;
            }

            if ($headers === null) {
                $headers = $this->normalizeHeaders($row);

                continue;
            }

            if ($this->isBlankRow($row)) {
                continue;
            }

            $mapped = $this->mapRow($headers, $row, $lineNumber);

            if ($mapped === null) {
                continue;
            }

            $items[] = $mapped;
        }

        fclose($handle);

        return $items;
    }

    /**
     * @param  array<int, string|null>  $row
     * @return array<int, string>
     */
    private function normalizeHeaders(array $row): array
    {
        return array_map(
            fn (?string $header): string => strtolower(trim((string) $header)),
            $row,
        );
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, string|null>  $row
     * @return array<string, mixed>|null
     */
    private function mapRow(array $headers, array $row, int $lineNumber): ?array
    {
        $data = [];

        foreach ($headers as $index => $header) {
            if ($header === '') {
                continue;
            }

            $data[$header] = trim((string) ($row[$index] ?? ''));
        }

        $productCode = $data['sku']
            ?? $data['barcode']
            ?? $data['product']
            ?? $data['product_sku']
            ?? '';

        if ($productCode === '') {
            throw new InvalidArgumentException("Row {$lineNumber} is missing a product SKU or barcode.");
        }

        $product = app(ProductLookupService::class)->findByBarcodeOrSku($productCode);

        if (! $product) {
            throw new InvalidArgumentException("Row {$lineNumber}: no active product found for \"{$productCode}\".");
        }

        $qty = (int) ($data['qty'] ?? $data['quantity'] ?? 0);

        if ($qty < 1) {
            throw new InvalidArgumentException("Row {$lineNumber}: quantity must be at least 1.");
        }

        $item = InventoryForm::buildLineItemFromProduct($product, $qty);

        if (filled($data['purchase_rate'] ?? null)) {
            $item['purchase_rate'] = (float) $data['purchase_rate'];
        }

        if (filled($data['rate_a'] ?? null)) {
            $item['rate_a'] = (float) $data['rate_a'];
        }

        if (filled($data['mrp'] ?? null)) {
            $item['mrp'] = (float) $data['mrp'];
        }

        if (filled($data['expiry_date'] ?? null)) {
            $item['expiry_date'] = Carbon::parse($data['expiry_date'])->toDateString();
        }

        return InventoryForm::normalizeItemData($item);
    }

    /**
     * @param  array<int, string|null>  $row
     */
    private function isBlankRow(array $row): bool
    {
        return collect($row)->every(fn (?string $value): bool => blank($value));
    }

    /**
     * @return array<int, string>
     */
    public static function exampleHeaders(): array
    {
        return ['sku', 'qty', 'purchase_rate', 'rate_a', 'mrp', 'expiry_date'];
    }

    /**
     * @return array<int, string>
     */
    public static function exampleRow(): array
    {
        return ['AMUL-MILK-1L', '10', '25', '30', '35', '2026-12-31'];
    }
}
