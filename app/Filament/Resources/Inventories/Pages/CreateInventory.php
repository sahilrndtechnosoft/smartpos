<?php

namespace App\Filament\Resources\Inventories\Pages;

use App\Filament\Imports\InventoryItemImporter;
use App\Filament\Resources\Concerns\AlignsFormActionsStart;
use App\Filament\Resources\Concerns\ImportsInventoryItemsFromCsv;
use App\Filament\Resources\Concerns\InteractsWithPosBarcode;
use App\Filament\Resources\Concerns\InteractsWithSecondaryPurchase;
use App\Filament\Resources\Inventories\InventoryResource;
use App\Filament\Resources\Inventories\Schemas\InventoryForm;
use App\Models\Product;
use App\Support\StockAdjuster;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateInventory extends CreateRecord
{
    use AlignsFormActionsStart;
    use ImportsInventoryItemsFromCsv;
    use InteractsWithPosBarcode;
    use InteractsWithSecondaryPurchase;

    protected static string $resource = InventoryResource::class;

    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'code' => 'INV-'.Str::upper(Str::random(8)),
            'date' => now(),
            'status' => 'pending',
            'tax_inclusive' => false,
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->secondaryPurchaseAction(),
            $this->getImportInventoryItemsAction(),
        ];
    }

    protected function posLineItemsStatePath(): string
    {
        return 'items';
    }

    /**
     * @param  array<int, array<string, mixed>>  $existingItems
     * @return array<string, mixed>
     */
    protected function buildPosLineItem(Product $product, array $existingItems): array
    {
        return InventoryForm::buildLineItemFromProduct($product);
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function refreshPosLineItem(Product $product, array $item): array
    {
        return InventoryForm::normalizeItemData($item);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['code'] ?? null)) {
            $data['code'] = 'INV-'.Str::upper(Str::random(8));
        }

        $this->data['items'] = InventoryForm::recalculateAllItemsTax(
            $this->data['items'] ?? [],
            (bool) ($data['tax_inclusive'] ?? false),
        );

        return $data;
    }

    protected function afterCreate(): void
    {
        StockAdjuster::apply(StockAdjuster::qtyByProduct($this->record->items));
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
