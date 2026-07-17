<?php

namespace App\Filament\Resources\CreditNotes\Pages;

use App\Filament\Resources\CreditNotes\CreditNoteResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCreditNote extends ViewRecord
{
    protected static string $resource = CreditNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            EditAction::make(),
        ];
    }
}
