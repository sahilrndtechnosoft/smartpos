<?php

namespace App\Filament\Resources\ContraEntries\Pages;

use App\Filament\Resources\ContraEntries\ContraEntryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewContraEntry extends ViewRecord
{
    protected static string $resource = ContraEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            EditAction::make(),
        ];
    }
}
