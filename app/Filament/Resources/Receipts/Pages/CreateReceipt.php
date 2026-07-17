<?php

namespace App\Filament\Resources\Receipts\Pages;

use App\Filament\Resources\Concerns\AlignsFormActionsStart;
use App\Filament\Resources\Receipts\ReceiptResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateReceipt extends CreateRecord
{
    use AlignsFormActionsStart;

    protected static string $resource = ReceiptResource::class;

    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'code' => 'RCT-'.Str::upper(Str::random(8)),
            'receipt_date' => now(),
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['code'] ?? null)) {
            $data['code'] = 'RCT-'.Str::upper(Str::random(8));
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->postLedgerEntries();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
