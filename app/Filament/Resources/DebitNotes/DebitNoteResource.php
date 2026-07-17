<?php

namespace App\Filament\Resources\DebitNotes;

use App\Filament\Resources\Concerns\AuthorizesModuleAccess;
use App\Filament\Resources\DebitNotes\Pages\CreateDebitNote;
use App\Filament\Resources\DebitNotes\Pages\EditDebitNote;
use App\Filament\Resources\DebitNotes\Pages\ListDebitNotes;
use App\Filament\Resources\DebitNotes\Pages\ViewDebitNote;
use App\Filament\Resources\DebitNotes\Schemas\DebitNoteForm;
use App\Filament\Resources\DebitNotes\Schemas\DebitNoteInfolist;
use App\Filament\Resources\DebitNotes\Tables\DebitNotesTable;
use App\Models\DebitNote;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class DebitNoteResource extends Resource
{
    use AuthorizesModuleAccess;

    protected static ?string $model = DebitNote::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMinusCircle;

    protected static ?string $navigationLabel = 'Debit Notes';

    protected static ?string $modelLabel = 'Debit Note';

    protected static ?string $pluralModelLabel = 'Debit Notes';

    protected static string|UnitEnum|null $navigationGroup = 'Accounts';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?string $slug = 'debit-notes';

    public static function form(Schema $schema): Schema
    {
        return DebitNoteForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DebitNoteInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DebitNotesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDebitNotes::route('/'),
            'create' => CreateDebitNote::route('/create'),
            'view' => ViewDebitNote::route('/{record}'),
            'edit' => EditDebitNote::route('/{record}/edit'),
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
        return 'debit_notes';
    }
}
