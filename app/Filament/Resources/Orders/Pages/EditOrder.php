<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Actions\PrintDocumentAction;
use App\Filament\Resources\Concerns\AlignsFormActionsStart;
use App\Filament\Resources\Concerns\InteractsWithPosBarcode;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Models\Product;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    use AlignsFormActionsStart;
    use InteractsWithPosBarcode;

    protected static string $resource = OrderResource::class;

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
        return OrderForm::buildLineItemFromProduct($product);
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function refreshPosLineItem(Product $product, array $item): array
    {
        return OrderForm::normalizeItemData($item);
    }

    protected function afterPosLineItemAdded(): void
    {
        foreach (OrderForm::calculateOrderTotals($this->data['items'] ?? []) as $field => $value) {
            $this->data[$field] = $value;
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return OrderForm::applyOrderTotals($data);
    }

    protected function getHeaderActions(): array
    {
        return [
            PrintDocumentAction::make('print', 'Print invoice', 'print.orders.invoice'),
            ActionGroup::make([
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
                ->tooltip('Actions'),
        ];
    }

    protected function afterSave(): void
    {
        $this->record->recalculateTotals();
        $this->record->refresh();
    }
}
