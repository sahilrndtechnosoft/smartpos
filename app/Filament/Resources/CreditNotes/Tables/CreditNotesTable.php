<?php

namespace App\Filament\Resources\CreditNotes\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CreditNotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Credit note number')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('note_date')
                    ->label('Date')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),

                TextColumn::make('amount')
                    ->money('INR')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('reason')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultSort('note_date', 'desc')
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
            ->emptyStateHeading('No credit notes yet');
    }
}
