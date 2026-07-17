<?php

namespace App\Filament\Resources\ContraEntries\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ContraEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Contra number')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('entry_date')
                    ->label('Date')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('fromLedgerAccount.name')
                    ->label('From'),

                TextColumn::make('toLedgerAccount.name')
                    ->label('To'),

                TextColumn::make('amount')
                    ->money('INR')
                    ->sortable()
                    ->alignEnd(),
            ])
            ->defaultSort('entry_date', 'desc')
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
            ->emptyStateHeading('No contra entries yet');
    }
}
