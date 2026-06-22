<?php

namespace App\Filament\Resources\KeyboardShortcuts\Pages;

use App\Filament\Resources\Concerns\AlignsFormActionsStart;
use App\Filament\Resources\KeyboardShortcuts\KeyboardShortcutResource;
use App\Support\KeyboardShortcutData;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditKeyboardShortcut extends EditRecord
{
    use AlignsFormActionsStart;

    protected static string $resource = KeyboardShortcutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
                ->tooltip('Actions'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return KeyboardShortcutData::normalize($data);
    }
}
