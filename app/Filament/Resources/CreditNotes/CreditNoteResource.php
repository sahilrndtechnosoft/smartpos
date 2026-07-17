<?php

namespace App\Filament\Resources\CreditNotes;

use App\Filament\Resources\Concerns\AuthorizesModuleAccess;
use App\Filament\Resources\CreditNotes\Pages\CreateCreditNote;
use App\Filament\Resources\CreditNotes\Pages\EditCreditNote;
use App\Filament\Resources\CreditNotes\Pages\ListCreditNotes;
use App\Filament\Resources\CreditNotes\Pages\ViewCreditNote;
use App\Filament\Resources\CreditNotes\Schemas\CreditNoteForm;
use App\Filament\Resources\CreditNotes\Schemas\CreditNoteInfolist;
use App\Filament\Resources\CreditNotes\Tables\CreditNotesTable;
use App\Models\CreditNote;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CreditNoteResource extends Resource
{
    use AuthorizesModuleAccess;

    protected static ?string $model = CreditNote::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPlusCircle;

    protected static ?string $navigationLabel = 'Credit Notes';

    protected static ?string $modelLabel = 'Credit Note';

    protected static ?string $pluralModelLabel = 'Credit Notes';

    protected static string|UnitEnum|null $navigationGroup = 'Accounts';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?string $slug = 'credit-notes';

    public static function form(Schema $schema): Schema
    {
        return CreditNoteForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CreditNoteInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CreditNotesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCreditNotes::route('/'),
            'create' => CreateCreditNote::route('/create'),
            'view' => ViewCreditNote::route('/{record}'),
            'edit' => EditCreditNote::route('/{record}/edit'),
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
        return 'credit_notes';
    }
}
