<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Concerns\HasFormActionsAtTopAndBottom;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditUser extends EditRecord
{
    use HasFormActionsAtTopAndBottom;

    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                DeleteAction::make()
                    ->hidden(fn (): bool => $this->record->is(Auth::user())),
            ])
                ->tooltip('Actions'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['is_verified'] = filled($this->record->email_verified_at);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['email_verified_at'] = ! empty($data['is_verified'])
            ? ($this->record->email_verified_at ?? now())
            : null;

        unset($data['is_verified'], $data['passwordConfirmation']);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        if (! auth()->user()?->isSuperAdmin()) {
            unset($data['is_super_admin']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncModulePermissions();
    }
}
