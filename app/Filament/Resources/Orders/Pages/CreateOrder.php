<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Concerns\AlignsFormActionsStart;
use App\Filament\Resources\Concerns\InteractsWithPosBarcode;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Models\Customer;
use App\Models\Product;
use App\Support\StockAdjuster;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateOrder extends CreateRecord
{
    use AlignsFormActionsStart;
    use InteractsWithPosBarcode;

    protected static string $resource = OrderResource::class;

    /**
     * @var list<array{method: string, amount: float}>
     */
    protected array $resolvedPayments = [];

    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'code' => 'SO-'.Str::upper(Str::random(8)),
            'customer_id' => Customer::cashCustomer()->id,
            'ordered_at' => now(),
            'payment_mode' => 'cod',
            'total' => 0,
            'discount_total' => 0,
            'grand_total' => 0,
            'primary_total' => 0,
        ]);
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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['code'] ?? null)) {
            $data['code'] = 'SO-'.Str::upper(Str::random(8));
        }

        $this->resolvedPayments = OrderForm::resolvePayments(
            $data['payment_mode'] ?? null,
            $this->data['items'] ?? [],
            $data['splitPayments'] ?? [],
        );

        unset($data['splitPayments']);

        return OrderForm::applyOrderTotals($data);
    }

    protected function afterCreate(): void
    {
        $this->record->recalculateTotals();
        $this->record->refresh();

        OrderForm::syncPayments($this->record, $this->resolvedPayments);

        StockAdjuster::apply(StockAdjuster::negate(
            StockAdjuster::qtyByProduct($this->record->items),
        ));
    }

    /**
     * Bound to the POS "save bill" keyboard shortcut (F9).
     */
    public function saveBill(): void
    {
        $this->create();
    }

    protected function getCreatedNotification(): ?Notification
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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
