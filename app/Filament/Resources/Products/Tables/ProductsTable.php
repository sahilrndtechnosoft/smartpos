<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Brand;
use App\Models\Product;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Product')
                    ->searchable(['name', 'sku', 'barcode'])
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Product $record): ?string => filled($record->sku)
                        ? "SKU: {$record->sku}"
                        : null),

                TextColumn::make('brand.name')
                    ->label('Company')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('categories.name')
                    ->label('Categories')
                    ->badge()
                    ->separator(',')
                    ->placeholder('None')
                    ->toggleable(),

                TextColumn::make('qty')
                    ->label('Qty')
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('mrp')
                    ->label('MRP')
                    ->money('INR')
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('unit')
                    ->label('Unit')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),

                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->sortable(),

                TextColumn::make('expired_at')
                    ->label('Expiry')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Added')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->striped()
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All products')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                SelectFilter::make('brand_id')
                    ->label('Company')
                    ->options(fn (): array => Brand::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload(),

                SelectFilter::make('categories')
                    ->label('Category')
                    ->relationship('categories', 'name')
                    ->searchable()
                    ->preload(),

                TrashedFilter::make(),
            ])
            ->filtersFormColumns(2)
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->tooltip('Actions'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No products yet')
            ->emptyStateDescription('Create your first product to start managing inventory.')
            ->emptyStateIcon(Heroicon::OutlinedRectangleStack);
    }
}
