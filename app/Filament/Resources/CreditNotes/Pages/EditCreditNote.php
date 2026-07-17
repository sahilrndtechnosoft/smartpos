<?php

namespace App\Filament\Resources\CreditNotes\Pages;

use App\Filament\Resources\Concerns\AlignsFormActionsStart;
use App\Filament\Resources\CreditNotes\CreditNoteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCreditNote extends EditRecord
{
    use AlignsFormActionsStart;

    protected static string $resource = CreditNoteResource::class;

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
