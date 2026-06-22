<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Concerns\AlignsFormActionsStart;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateOrder extends CreateRecord
{
    use AlignsFormActionsStart;

    protected static string $resource = OrderResource::class;

    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'code' => 'SO-'.Str::upper(Str::random(8)),
            'ordered_at' => now(),
            'payment_mode' => 'cod',
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['code'] ?? null)) {
            $data['code'] = 'SO-'.Str::upper(Str::random(8));
        }

        return $data;
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
