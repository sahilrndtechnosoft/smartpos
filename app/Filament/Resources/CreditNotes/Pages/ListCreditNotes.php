<?php

namespace App\Filament\Resources\CreditNotes\Pages;

use App\Filament\Resources\CreditNotes\CreditNoteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListCreditNotes extends ListRecords
{
    protected static string $resource = CreditNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New credit note')
                ->icon(Heroicon::Plus),
        ];
    }
}
