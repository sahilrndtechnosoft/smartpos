<?php

namespace App\Filament\Resources\Damages\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class DamagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Damage number')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('qty')
                    ->label('Qty')
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('cost_value')
                    ->label('Cost value')
                    ->money('INR')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('reason')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('damaged_at')
                    ->label('Date')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('damaged_at', 'desc')
            ->striped()
            ->filters([
                TrashedFilter::make(),
            ])
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
            ->emptyStateHeading('No damages recorded yet');
    }
}
