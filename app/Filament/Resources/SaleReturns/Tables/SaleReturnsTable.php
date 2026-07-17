<?php

namespace App\Filament\Resources\SaleReturns\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SaleReturnsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('SR number')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('order.code')
                    ->label('Original SO')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),

                TextColumn::make('returned_at')
                    ->label('Returned at')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('refund_mode')
                    ->label('Refund')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => strtoupper($state))
                    ->sortable(),

                TextColumn::make('total')
                    ->label('Refund total')
                    ->money('INR')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->date('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('returned_at', 'desc')
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
            ->emptyStateHeading('No sale returns yet');
    }
}
