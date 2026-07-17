<?php

namespace App\Filament\Resources\ContraEntries\Pages;

use App\Filament\Resources\Concerns\AlignsFormActionsStart;
use App\Filament\Resources\ContraEntries\ContraEntryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContraEntry extends EditRecord
{
    use AlignsFormActionsStart;

    protected static string $resource = ContraEntryResource::class;

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
