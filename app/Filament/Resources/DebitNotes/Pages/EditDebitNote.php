<?php

namespace App\Filament\Resources\DebitNotes\Pages;

use App\Filament\Resources\Concerns\AlignsFormActionsStart;
use App\Filament\Resources\DebitNotes\DebitNoteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDebitNote extends EditRecord
{
    use AlignsFormActionsStart;

    protected static string $resource = DebitNoteResource::class;

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
