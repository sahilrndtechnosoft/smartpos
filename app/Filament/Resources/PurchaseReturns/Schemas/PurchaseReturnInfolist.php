<?php

namespace App\Filament\Resources\PurchaseReturns\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PurchaseReturnInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Return details')
                    ->schema([
                        Grid::make()
                            ->columns(4)
                            ->schema([
                                TextEntry::make('code')
                                    ->label('PR number')
                                    ->weight('medium'),

                                TextEntry::make('inventory.code')
                                    ->label('Original purchase'),

                                TextEntry::make('supplier.name')
                                    ->label('Supplier')
                                    ->placeholder('—'),

                                TextEntry::make('returned_at')
                                    ->label('Returned at')
                                    ->dateTime('M j, Y H:i:s'),

                                TextEntry::make('refund_mode')
                                    ->label('Refund via')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),

                                TextEntry::make('reason')
                                    ->placeholder('—')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Returned items')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('Items')
                            ->schema([
                                Grid::make()
                                    ->columns(4)
                                    ->schema([
                                        TextEntry::make('product_name')
                                            ->label('Product')
                                            ->weight('medium'),

                                        TextEntry::make('qty')
                                            ->label('Qty'),

                                        TextEntry::make('purchase_rate')
                                            ->label('Cost price')
                                            ->money('INR'),

                                        TextEntry::make('amount')
                                            ->label('Amount')
                                            ->money('INR'),
                                    ]),
                            ])
                            ->contained()
                            ->columnSpanFull(),
                    ]),

                Section::make('Totals')
                    ->schema([
                        TextEntry::make('total')
                            ->label('Refund total')
                            ->money('INR')
                            ->weight('bold'),
                    ]),
            ]);
    }
}
