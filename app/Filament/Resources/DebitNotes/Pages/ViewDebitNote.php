<?php

namespace App\Filament\Resources\DebitNotes\Pages;

use App\Filament\Resources\DebitNotes\DebitNoteResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDebitNote extends ViewRecord
{
    protected static string $resource = DebitNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            EditAction::make(),
        ];
    }
}
