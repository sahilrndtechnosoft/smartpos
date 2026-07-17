<?php

namespace App\Filament\Resources\SaleReturns;

use App\Filament\Resources\Concerns\AuthorizesModuleAccess;
use App\Filament\Resources\SaleReturns\Pages\CreateSaleReturn;
use App\Filament\Resources\SaleReturns\Pages\EditSaleReturn;
use App\Filament\Resources\SaleReturns\Pages\ListSaleReturns;
use App\Filament\Resources\SaleReturns\Pages\ViewSaleReturn;
use App\Filament\Resources\SaleReturns\Schemas\SaleReturnForm;
use App\Filament\Resources\SaleReturns\Schemas\SaleReturnInfolist;
use App\Filament\Resources\SaleReturns\Tables\SaleReturnsTable;
use App\Models\SaleReturn;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class SaleReturnResource extends Resource
{
    use AuthorizesModuleAccess;

    protected static ?string $model = SaleReturn::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUturnLeft;

    protected static ?string $navigationLabel = 'Sale Returns';

    protected static ?string $modelLabel = 'Sale Return';

    protected static ?string $pluralModelLabel = 'Sale Returns';

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?string $slug = 'sale-returns';

    public static function form(Schema $schema): Schema
    {
        return SaleReturnForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SaleReturnInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SaleReturnsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSaleReturns::route('/'),
            'create' => CreateSaleReturn::route('/create'),
            'view' => ViewSaleReturn::route('/{record}'),
            'edit' => EditSaleReturn::route('/{record}/edit'),
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
        return 'sale_returns';
    }
}
