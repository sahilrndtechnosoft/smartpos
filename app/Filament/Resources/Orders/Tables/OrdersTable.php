<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Filament\Actions\PrintDocumentAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('SO number')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.phone')
                    ->label('Phone')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('ordered_at')
                    ->label('Ordered at')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('payment_mode')
                    ->label('Payment')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => strtoupper($state))
                    ->sortable(),

                TextColumn::make('grand_total')
                    ->label('Grand total')
                    ->money('INR')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->date('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('ordered_at', 'desc')
            ->striped()
            ->filters([
                SelectFilter::make('payment_mode')
                    ->label('Payment mode')
                    ->options([
                        'cod' => 'COD',
                        'online' => 'Online',
                        'card' => 'Card',
                        'upi' => 'UPI',
                        'wallet' => 'Wallet',
                    ]),

                SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    PrintDocumentAction::make('print', 'Print invoice', 'print.orders.invoice'),
                    DeleteAction::make(),
                ])
                    ->tooltip('Actions'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No sales orders yet')
            ->emptyStateDescription('Create a sales order for a customer.')
            ->emptyStateIcon(Heroicon::OutlinedShoppingCart);
    }
}
