<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Users\Pages\ViewUser;
use App\Support\ModuleRegistry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PermissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'modulePermissions';

    protected static ?string $title = 'Module permissions';

    protected static ?string $recordTitleAttribute = 'module';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public function table(Table $table): Table
    {
        $ownerIsSuperAdmin = $this->getOwnerRecord()->isSuperAdmin();
        $isReadOnly = is_a($this->pageClass, ViewUser::class, true) || $ownerIsSuperAdmin;

        return $table
            ->columns([
                TextColumn::make('module')
                    ->label('Module')
                    ->formatStateUsing(fn (string $state): string => ModuleRegistry::label($state))
                    ->weight('medium'),
                ...$this->getPermissionColumns($isReadOnly),
            ])
            ->paginated(false)
            ->defaultSort('module')
            ->emptyStateHeading('No modules configured')
            ->emptyStateDescription('Module permissions will appear here once synced.')
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([])
            ->description($ownerIsSuperAdmin
                ? 'Super admins always have full access to every module.'
                : 'Control which modules this user can view, create, edit, or delete.');
    }

    /**
     * @return array<int, IconColumn|ToggleColumn>
     */
    protected function getPermissionColumns(bool $readOnly): array
    {
        $fields = [
            'can_view' => 'View',
            'can_create' => 'Create',
            'can_edit' => 'Edit',
            'can_delete' => 'Delete',
        ];

        if ($readOnly) {
            return collect($fields)
                ->map(fn (string $label, string $field): IconColumn => IconColumn::make($field)
                    ->label($label)
                    ->boolean())
                ->values()
                ->all();
        }

        return collect($fields)
            ->map(fn (string $label, string $field): ToggleColumn => ToggleColumn::make($field)
                ->label($label))
            ->values()
            ->all();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    protected function makeTable(): Table
    {
        $this->getOwnerRecord()->syncModulePermissions();

        return parent::makeTable();
    }
}
