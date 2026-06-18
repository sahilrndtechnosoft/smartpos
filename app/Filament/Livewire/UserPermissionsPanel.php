<?php

namespace App\Filament\Livewire;

use App\Filament\Resources\Users\Schemas\UserPermissionsSchema;
use App\Models\User;
use App\Models\UserModulePermission;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class UserPermissionsPanel extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public User $record;

    public bool $readOnly = false;

    /** @var array<string, mixed> */
    public array $data = [];

    public function mount(User $record, bool $readOnly = false): void
    {
        $this->record = $record;
        $this->readOnly = $readOnly;

        $this->record->syncModulePermissions();
        $this->record->load('modulePermissions');

        $this->data = [
            'permissions' => $this->buildPermissionState(),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return UserPermissionsSchema::configure($schema, $this->record, $this->readOnly);
    }

    public function updatedData(): void
    {
        if ($this->readOnly || $this->record->isSuperAdmin()) {
            return;
        }

        $this->savePermissions();
    }

    protected function savePermissions(): void
    {
        $permissions = $this->data['permissions'] ?? [];

        foreach ($permissions as $module => $values) {
            UserModulePermission::query()->updateOrCreate(
                [
                    'user_id' => $this->record->id,
                    'module' => $module,
                ],
                [
                    'can_view' => (bool) ($values['can_view'] ?? false),
                    'can_create' => (bool) ($values['can_create'] ?? false),
                    'can_edit' => (bool) ($values['can_edit'] ?? false),
                    'can_delete' => (bool) ($values['can_delete'] ?? false),
                ],
            );
        }

        $this->record->load('modulePermissions');

        Notification::make()
            ->title('Permissions updated')
            ->success()
            ->duration(2000)
            ->send();
    }

    /**
     * @return array<string, array{can_view: bool, can_create: bool, can_edit: bool, can_delete: bool}>
     */
    protected function buildPermissionState(): array
    {
        $state = [];

        foreach ($this->record->modulePermissions as $permission) {
            $state[$permission->module] = [
                'can_view' => $permission->can_view,
                'can_create' => $permission->can_create,
                'can_edit' => $permission->can_edit,
                'can_delete' => $permission->can_delete,
            ];
        }

        return $state;
    }

    public function render(): View
    {
        return view('filament.users.permissions-panel');
    }
}
