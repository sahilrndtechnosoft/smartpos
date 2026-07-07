<?php

namespace App\Filament\Resources\Concerns;

use App\Models\Product;
use App\Services\ProductLookupService;
use Filament\Notifications\Notification;

trait InteractsWithPosBarcode
{
    abstract protected function posLineItemsStatePath(): string;

    /**
     * @param  array<int, array<string, mixed>>  $existingItems
     * @return array<string, mixed>
     */
    abstract protected function buildPosLineItem(Product $product, array $existingItems): array;

    protected function afterPosLineItemAdded(): void {}

    public function scanBarcode(?string $barcode = null): void
    {
        $barcode = trim($barcode ?? (string) ($this->data['barcodeScan'] ?? ''));

        if ($barcode === '') {
            $this->dispatch('pos-barcode-focus');

            return;
        }

        $product = app(ProductLookupService::class)->findByBarcodeOrSku($barcode);

        if (! $product) {
            Notification::make()
                ->title('Product not found')
                ->body("No active product matches barcode or SKU \"{$barcode}\".")
                ->danger()
                ->send();

            $this->data['barcodeScan'] = '';
            $this->dispatch('pos-barcode-focus');

            return;
        }

        $path = $this->posLineItemsStatePath();
        $items = $this->data[$path] ?? [];
        $matched = false;

        foreach ($items as $index => $item) {
            if (($item['product_id'] ?? null) !== $product->id) {
                continue;
            }

            $items[$index]['qty'] = (int) ($item['qty'] ?? 0) + 1;
            $items[$index] = $this->refreshPosLineItem($product, $items[$index]);
            $matched = true;

            break;
        }

        if (! $matched) {
            $items[] = $this->buildPosLineItem($product, $items);
        }

        $this->data[$path] = array_values($items);
        $this->data['barcodeScan'] = '';
        $this->afterPosLineItemAdded();

        Notification::make()
            ->title($product->name)
            ->body('Line item updated.')
            ->success()
            ->duration(1500)
            ->send();

        $this->dispatch('pos-barcode-focus');
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function refreshPosLineItem(Product $product, array $item): array
    {
        return $item;
    }
}
