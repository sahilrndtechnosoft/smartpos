<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Actions\PrintDocumentAction;
use App\Filament\Resources\Concerns\AlignsFormActionsStart;
use App\Filament\Resources\Concerns\InteractsWithPosBarcode;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Models\Product;
use App\Support\StockAdjuster;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    use AlignsFormActionsStart;
    use InteractsWithPosBarcode;

    protected static string $resource = OrderResource::class;

    /**
     * @var list<array{method: string, amount: float}>
     */
    protected array $resolvedPayments = [];

    /**
     * @var array<string, int>
     */
    protected array $stockQtyBeforeSave = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['splitPayments'] = OrderForm::splitPaymentsFromOrder($this->record);

        return $data;
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
        return OrderForm::buildLineItemFromProduct($product);
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function refreshPosLineItem(Product $product, array $item): array
    {
        return OrderForm::reapplyRateForQty($product, $item);
    }

    protected function afterPosLineItemAdded(): void
    {
        $this->data['items'] = OrderForm::applySchemes($this->data['items'] ?? []);

        foreach (OrderForm::calculateOrderTotals($this->data['items']) as $field => $value) {
            $this->data[$field] = $value;
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->stockQtyBeforeSave = StockAdjuster::qtyByProduct($this->record->items()->get());

        $this->resolvedPayments = OrderForm::resolvePayments(
            $data['payment_mode'] ?? null,
            $this->data['items'] ?? [],
            $data['splitPayments'] ?? [],
        );

        unset($data['splitPayments']);

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

        OrderForm::syncPayments($this->record, $this->resolvedPayments);

        $stockQtyAfterSave = StockAdjuster::qtyByProduct($this->record->items()->get());

        // A sale increasing means more stock goes out, so the sign is inverted.
        StockAdjuster::apply(StockAdjuster::negate(
            StockAdjuster::diff($this->stockQtyBeforeSave, $stockQtyAfterSave),
        ));
    }

    /**
     * Bound to the POS "save bill" keyboard shortcut (F9).
     */
    public function saveBill(): void
    {
        $this->save();
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Bill saved')
            ->body("Sales order {$this->record->code} was saved successfully.")
            ->persistent()
            ->actions([
                Action::make('print')
                    ->label('Print invoice')
                    ->url(route('print.orders.invoice', $this->record))
                    ->openUrlInNewTab(),
            ]);
    }
}
