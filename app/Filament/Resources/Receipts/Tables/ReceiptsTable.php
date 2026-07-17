<?php

namespace App\Filament\Resources\Receipts\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ReceiptsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Receipt number')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('receipt_date')
                    ->label('Date')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Received from')
                    ->searchable(),

                TextColumn::make('ledgerAccount.name')
                    ->label('Deposited to'),

                TextColumn::make('amount')
                    ->money('INR')
                    ->sortable()
                    ->alignEnd(),
            ])
            ->defaultSort('receipt_date', 'desc')
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
            ->emptyStateHeading('No receipts yet');
    }
}
