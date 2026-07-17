<?php

namespace App\Filament\Resources\Receipts\Pages;

use App\Filament\Resources\Concerns\AlignsFormActionsStart;
use App\Filament\Resources\Receipts\ReceiptResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReceipt extends EditRecord
{
    use AlignsFormActionsStart;

    protected static string $resource = ReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->record->postLedgerEntries();
    }
}
