<?php

namespace App\Filament\Resources\KeyboardShortcuts\Pages;

use App\Filament\Resources\KeyboardShortcuts\KeyboardShortcutResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListKeyboardShortcuts extends ListRecords
{
    protected static string $resource = KeyboardShortcutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New shortcut')
                ->icon(Heroicon::Plus),
        ];
    }
}
