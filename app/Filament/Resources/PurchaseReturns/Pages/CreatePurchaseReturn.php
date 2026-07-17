<?php

namespace App\Filament\Resources\PurchaseReturns\Pages;

use App\Filament\Resources\Concerns\AlignsFormActionsStart;
use App\Filament\Resources\PurchaseReturns\PurchaseReturnResource;
use App\Filament\Resources\PurchaseReturns\Schemas\PurchaseReturnForm;
use App\Support\StockAdjuster;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreatePurchaseReturn extends CreateRecord
{
    use AlignsFormActionsStart;

    protected static string $resource = PurchaseReturnResource::class;

    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'code' => 'PR-'.Str::upper(Str::random(8)),
            'returned_at' => now(),
            'refund_mode' => 'cod',
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['code'] ?? null)) {
            $data['code'] = 'PR-'.Str::upper(Str::random(8));
        }

        $data['total'] = round((float) collect($this->data['items'] ?? [])->sum('amount'), 2);

        unset($data['items'], $data['current_return_id']);

        return $data;
    }

    protected function afterCreate(): void
    {
        PurchaseReturnForm::persistItems($this->record, $this->data['items'] ?? []);
        $this->record->recalculateTotal();
        $this->record->refresh();

        StockAdjuster::apply(StockAdjuster::negate(
            StockAdjuster::qtyByProduct($this->record->items),
        ));

        PurchaseReturnForm::recordRefundPayment($this->record);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
