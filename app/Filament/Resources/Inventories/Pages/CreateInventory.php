<?php

namespace App\Filament\Resources\Inventories\Pages;

use App\Filament\Imports\InventoryItemImporter;
use App\Filament\Resources\Concerns\AlignsFormActionsStart;
use App\Filament\Resources\Concerns\ImportsInventoryItemsFromCsv;
use App\Filament\Resources\Concerns\InteractsWithPosBarcode;
use App\Filament\Resources\Inventories\InventoryResource;
use App\Filament\Resources\Inventories\Schemas\InventoryForm;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateInventory extends CreateRecord
{
    use AlignsFormActionsStart;
    use ImportsInventoryItemsFromCsv;
    use InteractsWithPosBarcode;

    protected static string $resource = InventoryResource::class;

    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'code' => 'INV-'.Str::upper(Str::random(8)),
            'date' => now(),
            'status' => 'pending',
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
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

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
