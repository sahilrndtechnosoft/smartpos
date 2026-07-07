<?php

namespace App\Filament\Resources\Inventories\Pages;

use App\Filament\Actions\PrintDocumentAction;
use App\Filament\Resources\Concerns\AlignsFormActionsStart;
use App\Filament\Resources\Concerns\ImportsInventoryItemsFromCsv;
use App\Filament\Resources\Concerns\InteractsWithPosBarcode;
use App\Filament\Resources\Inventories\InventoryResource;
use App\Filament\Resources\Inventories\Schemas\InventoryForm;
use App\Models\Product;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditInventory extends EditRecord
{
    use AlignsFormActionsStart;
    use ImportsInventoryItemsFromCsv;
    use InteractsWithPosBarcode;

    protected static string $resource = InventoryResource::class;

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

    protected function getHeaderActions(): array
    {
        return [
            $this->getImportInventoryItemsAction(),
            PrintDocumentAction::make('print', 'Print invoice', 'print.inventories.invoice'),
            ActionGroup::make([
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
                ->tooltip('Actions'),
        ];
    }
}
