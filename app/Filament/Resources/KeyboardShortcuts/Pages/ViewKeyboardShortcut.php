<?php

namespace App\Filament\Resources\KeyboardShortcuts\Pages;

use App\Filament\Resources\KeyboardShortcuts\KeyboardShortcutResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewKeyboardShortcut extends ViewRecord
{
    protected static string $resource = KeyboardShortcutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            EditAction::make(),
        ];
    }
}
