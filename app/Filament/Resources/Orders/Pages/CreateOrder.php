<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Concerns\AlignsFormActionsStart;
use App\Filament\Resources\Concerns\InteractsWithPosBarcode;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateOrder extends CreateRecord
{
    use AlignsFormActionsStart;
    use InteractsWithPosBarcode;

    protected static string $resource = OrderResource::class;

    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'code' => 'SO-'.Str::upper(Str::random(8)),
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
        return OrderForm::normalizeItemData($item);
    }

    protected function afterPosLineItemAdded(): void
    {
        foreach (OrderForm::calculateOrderTotals($this->data['items'] ?? []) as $field => $value) {
            $this->data[$field] = $value;
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['code'] ?? null)) {
            $data['code'] = 'SO-'.Str::upper(Str::random(8));
        }

        return OrderForm::applyOrderTotals($data);
    }

    protected function afterCreate(): void
    {
        $this->record->recalculateTotals();
        $this->record->refresh();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
