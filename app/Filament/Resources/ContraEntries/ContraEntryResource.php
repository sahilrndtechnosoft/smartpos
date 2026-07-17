<?php

namespace App\Filament\Resources\ContraEntries;

use App\Filament\Resources\Concerns\AuthorizesModuleAccess;
use App\Filament\Resources\ContraEntries\Pages\CreateContraEntry;
use App\Filament\Resources\ContraEntries\Pages\EditContraEntry;
use App\Filament\Resources\ContraEntries\Pages\ListContraEntries;
use App\Filament\Resources\ContraEntries\Pages\ViewContraEntry;
use App\Filament\Resources\ContraEntries\Schemas\ContraEntryForm;
use App\Filament\Resources\ContraEntries\Schemas\ContraEntryInfolist;
use App\Filament\Resources\ContraEntries\Tables\ContraEntriesTable;
use App\Models\ContraEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ContraEntryResource extends Resource
{
    use AuthorizesModuleAccess;

    protected static ?string $model = ContraEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $navigationLabel = 'Contra Entries';

    protected static ?string $modelLabel = 'Contra Entry';

    protected static ?string $pluralModelLabel = 'Contra Entries';

    protected static string|UnitEnum|null $navigationGroup = 'Accounts';

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?string $slug = 'contra-entries';

    public static function form(Schema $schema): Schema
    {
        return ContraEntryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ContraEntryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContraEntriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContraEntries::route('/'),
            'create' => CreateContraEntry::route('/create'),
            'view' => ViewContraEntry::route('/{record}'),
            'edit' => EditContraEntry::route('/{record}/edit'),
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
        return 'contra_entries';
    }
}
