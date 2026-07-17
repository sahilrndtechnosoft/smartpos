<?php

namespace App\Filament\Resources\SaleReturns\Pages;

use App\Filament\Resources\Concerns\AlignsFormActionsStart;
use App\Filament\Resources\SaleReturns\SaleReturnResource;
use App\Filament\Resources\SaleReturns\Schemas\SaleReturnForm;
use App\Support\StockAdjuster;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateSaleReturn extends CreateRecord
{
    use AlignsFormActionsStart;

    protected static string $resource = SaleReturnResource::class;

    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'code' => 'SR-'.Str::upper(Str::random(8)),
            'returned_at' => now(),
            'refund_mode' => 'cod',
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['code'] ?? null)) {
            $data['code'] = 'SR-'.Str::upper(Str::random(8));
        }

        $data['total'] = round((float) collect($this->data['items'] ?? [])->sum('amount'), 2);

        unset($data['items'], $data['current_return_id']);

        return $data;
    }

    protected function afterCreate(): void
    {
        SaleReturnForm::persistItems($this->record, $this->data['items'] ?? []);
        $this->record->recalculateTotal();
        $this->record->refresh();

        StockAdjuster::apply(StockAdjuster::qtyByProduct($this->record->items));

        SaleReturnForm::recordRefundPayment($this->record);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
