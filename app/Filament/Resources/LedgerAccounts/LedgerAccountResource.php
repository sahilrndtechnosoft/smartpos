<?php

namespace App\Filament\Resources\LedgerAccounts;

use App\Filament\Resources\Concerns\AuthorizesModuleAccess;
use App\Filament\Resources\LedgerAccounts\Pages\CreateLedgerAccount;
use App\Filament\Resources\LedgerAccounts\Pages\EditLedgerAccount;
use App\Filament\Resources\LedgerAccounts\Pages\ListLedgerAccounts;
use App\Filament\Resources\LedgerAccounts\Pages\ViewLedgerAccount;
use App\Filament\Resources\LedgerAccounts\Schemas\LedgerAccountForm;
use App\Filament\Resources\LedgerAccounts\Schemas\LedgerAccountInfolist;
use App\Filament\Resources\LedgerAccounts\Tables\LedgerAccountsTable;
use App\Models\LedgerAccount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class LedgerAccountResource extends Resource
{
    use AuthorizesModuleAccess;

    protected static ?string $model = LedgerAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $navigationLabel = 'Ledger Accounts';

    protected static ?string $modelLabel = 'Ledger Account';

    protected static ?string $pluralModelLabel = 'Ledger Accounts';

    protected static string|UnitEnum|null $navigationGroup = 'Accounts';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return LedgerAccountForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LedgerAccountInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LedgerAccountsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLedgerAccounts::route('/'),
            'create' => CreateLedgerAccount::route('/create'),
            'view' => ViewLedgerAccount::route('/{record}'),
            'edit' => EditLedgerAccount::route('/{record}/edit'),
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
        return 'ledger_accounts';
    }
}
