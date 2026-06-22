<?php

namespace App\Filament\Resources\KeyboardShortcuts;

use App\Filament\Resources\Concerns\AuthorizesModuleAccess;
use App\Filament\Resources\KeyboardShortcuts\Pages\CreateKeyboardShortcut;
use App\Filament\Resources\KeyboardShortcuts\Pages\EditKeyboardShortcut;
use App\Filament\Resources\KeyboardShortcuts\Pages\ListKeyboardShortcuts;
use App\Filament\Resources\KeyboardShortcuts\Pages\ViewKeyboardShortcut;
use App\Filament\Resources\KeyboardShortcuts\Schemas\KeyboardShortcutForm;
use App\Filament\Resources\KeyboardShortcuts\Schemas\KeyboardShortcutInfolist;
use App\Filament\Resources\KeyboardShortcuts\Tables\KeyboardShortcutsTable;
use App\Models\KeyboardShortcut;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class KeyboardShortcutResource extends Resource
{
    use AuthorizesModuleAccess;

    protected static ?string $model = KeyboardShortcut::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCommandLine;

    protected static ?string $navigationLabel = 'Keyboard shortcuts';

    protected static ?string $modelLabel = 'Keyboard shortcut';

    protected static ?string $pluralModelLabel = 'Keyboard shortcuts';

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 3;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'keyboard-shortcuts';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return KeyboardShortcutForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return KeyboardShortcutInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KeyboardShortcutsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKeyboardShortcuts::route('/'),
            'create' => CreateKeyboardShortcut::route('/create'),
            'view' => ViewKeyboardShortcut::route('/{record}'),
            'edit' => EditKeyboardShortcut::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    protected static function getModuleKey(): string
    {
        return 'keyboard_shortcuts';
    }
}
