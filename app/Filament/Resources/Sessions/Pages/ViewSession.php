<?php

namespace App\Filament\Resources\Sessions\Pages;

use App\Filament\Resources\Sessions\SessionResource;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSession extends ViewRecord
{
    protected static string $resource = SessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                DeleteAction::make()
                    ->label('Revoke')
                    ->modalHeading('Revoke session')
                    ->modalDescription('This will immediately sign the user out of this device.')
                    ->successNotificationTitle('Session revoked')
                    ->hidden(fn (): bool => $this->record->isCurrent()),
            ])
                ->tooltip('Actions'),
        ];
    }
}
