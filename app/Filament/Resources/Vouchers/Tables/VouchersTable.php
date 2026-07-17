<?php

namespace App\Filament\Resources\Vouchers\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class VouchersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Voucher number')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('voucher_date')
                    ->label('Date')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('debitLedgerAccount.name')
                    ->label('Debit'),

                TextColumn::make('creditLedgerAccount.name')
                    ->label('Credit'),

                TextColumn::make('amount')
                    ->money('INR')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('narration')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultSort('voucher_date', 'desc')
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
            ->emptyStateHeading('No vouchers yet');
    }
}
