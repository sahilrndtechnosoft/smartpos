<?php

namespace App\Filament\Resources\PurchaseReturns;

use App\Filament\Resources\Concerns\AuthorizesModuleAccess;
use App\Filament\Resources\PurchaseReturns\Pages\CreatePurchaseReturn;
use App\Filament\Resources\PurchaseReturns\Pages\EditPurchaseReturn;
use App\Filament\Resources\PurchaseReturns\Pages\ListPurchaseReturns;
use App\Filament\Resources\PurchaseReturns\Pages\ViewPurchaseReturn;
use App\Filament\Resources\PurchaseReturns\Schemas\PurchaseReturnForm;
use App\Filament\Resources\PurchaseReturns\Schemas\PurchaseReturnInfolist;
use App\Filament\Resources\PurchaseReturns\Tables\PurchaseReturnsTable;
use App\Models\PurchaseReturn;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PurchaseReturnResource extends Resource
{
    use AuthorizesModuleAccess;

    protected static ?string $model = PurchaseReturn::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUturnLeft;

    protected static ?string $navigationLabel = 'Purchase Returns';

    protected static ?string $modelLabel = 'Purchase Return';

    protected static ?string $pluralModelLabel = 'Purchase Returns';

    protected static string|UnitEnum|null $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?string $slug = 'purchase-returns';

    public static function form(Schema $schema): Schema
    {
        return PurchaseReturnForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PurchaseReturnInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PurchaseReturnsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPurchaseReturns::route('/'),
            'create' => CreatePurchaseReturn::route('/create'),
            'view' => ViewPurchaseReturn::route('/{record}'),
            'edit' => EditPurchaseReturn::route('/{record}/edit'),
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
        return 'purchase_returns';
    }
}
