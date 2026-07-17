<?php

namespace App\Filament\Resources\PurchaseReturns\Pages;

use App\Filament\Resources\Concerns\AlignsFormActionsStart;
use App\Filament\Resources\PurchaseReturns\PurchaseReturnResource;
use App\Filament\Resources\PurchaseReturns\Schemas\PurchaseReturnForm;
use App\Support\StockAdjuster;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseReturn extends EditRecord
{
    use AlignsFormActionsStart;

    protected static string $resource = PurchaseReturnResource::class;

    /**
     * @var array<string, int>
     */
    protected array $stockQtyBeforeSave = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['current_return_id'] = $this->record->id;
        $data['items'] = PurchaseReturnForm::itemsFromPurchaseReturn($this->record);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->stockQtyBeforeSave = StockAdjuster::qtyByProduct($this->record->items()->get());

        $data['total'] = round((float) collect($this->data['items'] ?? [])->sum('amount'), 2);

        unset($data['items'], $data['current_return_id']);

        return $data;
    }

    protected function afterSave(): void
    {
        PurchaseReturnForm::persistItems($this->record, $this->data['items'] ?? []);
        $this->record->recalculateTotal();
        $this->record->refresh();

        $stockQtyAfterSave = StockAdjuster::qtyByProduct($this->record->items()->get());

        // A return increasing means more stock goes back to the supplier.
        StockAdjuster::apply(StockAdjuster::negate(
            StockAdjuster::diff($this->stockQtyBeforeSave, $stockQtyAfterSave),
        ));

        PurchaseReturnForm::recordRefundPayment($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
                ->tooltip('Actions'),
        ];
    }
}
