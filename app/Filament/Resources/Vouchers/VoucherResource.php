<?php

namespace App\Filament\Resources\Vouchers;

use App\Filament\Resources\Concerns\AuthorizesModuleAccess;
use App\Filament\Resources\Vouchers\Pages\CreateVoucher;
use App\Filament\Resources\Vouchers\Pages\EditVoucher;
use App\Filament\Resources\Vouchers\Pages\ListVouchers;
use App\Filament\Resources\Vouchers\Pages\ViewVoucher;
use App\Filament\Resources\Vouchers\Schemas\VoucherForm;
use App\Filament\Resources\Vouchers\Schemas\VoucherInfolist;
use App\Filament\Resources\Vouchers\Tables\VouchersTable;
use App\Models\Voucher;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class VoucherResource extends Resource
{
    use AuthorizesModuleAccess;

    protected static ?string $model = Voucher::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Vouchers';

    protected static string|UnitEnum|null $navigationGroup = 'Accounts';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'code';

    public static function form(Schema $schema): Schema
    {
        return VoucherForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return VoucherInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VouchersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVouchers::route('/'),
            'create' => CreateVoucher::route('/create'),
            'view' => ViewVoucher::route('/{record}'),
            'edit' => EditVoucher::route('/{record}/edit'),
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
        return 'vouchers';
    }
}
