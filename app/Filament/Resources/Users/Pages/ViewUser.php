<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            ActionGroup::make([
                DeleteAction::make()
                    ->hidden(fn (): bool => $this->record->is(Auth::user())),
            ])
                ->tooltip('More actions'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['is_verified'] = filled($this->record->email_verified_at);

        return $data;
    }
}
