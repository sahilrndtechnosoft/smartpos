<?php

namespace App\Filament\Resources\DebitNotes\Pages;

use App\Filament\Resources\DebitNotes\DebitNoteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListDebitNotes extends ListRecords
{
    protected static string $resource = DebitNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New debit note')
                ->icon(Heroicon::Plus),
        ];
    }
}
