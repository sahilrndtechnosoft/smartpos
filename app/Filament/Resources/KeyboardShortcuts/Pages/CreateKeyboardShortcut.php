<?php

namespace App\Filament\Resources\KeyboardShortcuts\Pages;

use App\Filament\Resources\Concerns\AlignsFormActionsStart;
use App\Filament\Resources\KeyboardShortcuts\KeyboardShortcutResource;
use App\Support\KeyboardShortcutData;
use Filament\Resources\Pages\CreateRecord;

class CreateKeyboardShortcut extends CreateRecord
{
    use AlignsFormActionsStart;

    protected static string $resource = KeyboardShortcutResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return KeyboardShortcutData::normalize($data);
    }
}
