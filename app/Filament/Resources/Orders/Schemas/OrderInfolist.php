<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Support\ProductRateOptions;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Order details')
                    ->schema([
                        Grid::make()
                            ->columns(3)
                            ->schema([
                                TextEntry::make('code')
                                    ->label('SO number')
                                    ->weight('medium'),

                                TextEntry::make('customer.name')
                                    ->label('Customer')
                                    ->placeholder('—'),

                                TextEntry::make('ordered_at')
                                    ->label('Ordered at')
                                    ->dateTime('M j, Y H:i:s'),

                                TextEntry::make('payment_mode')
                                    ->label('Payment mode')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),
                            ]),
                    ]),

                Section::make('Line items')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('Items')
                            ->schema([
                                Grid::make()
                                    ->columns(5)
                                    ->schema([
                                        TextEntry::make('product_name')
                                            ->label('Product')
                                            ->weight('medium'),

                                        TextEntry::make('product_snapshot.applied_rate')
                                            ->label('Rate')
                                            ->formatStateUsing(fn (?string $state): string => ProductRateOptions::label($state ?? 'rate_a')),

                                        TextEntry::make('qty')
                                            ->label('Qty'),

                                        TextEntry::make('unit_price')
                                            ->label('Unit price')
                                            ->money('INR'),

                                        TextEntry::make('final_price')
                                            ->label('Amount')
                                            ->money('INR'),
                                    ]),

                                Grid::make()
                                    ->columns(3)
                                    ->schema([
                                        TextEntry::make('discount_amount')
                                            ->label('Discount')
                                            ->money('INR')
                                            ->placeholder('—'),

                                        TextEntry::make('tax_total')
                                            ->label('Tax')
                                            ->money('INR'),

                                        TextEntry::make('subtotal')
                                            ->label('Subtotal')
                                            ->money('INR'),
                                    ]),
                            ])
                            ->contained()
                            ->columnSpanFull(),
                    ]),

                Section::make('Totals')
                    ->schema([
                        Grid::make()
                            ->columns(4)
                            ->schema([
                                TextEntry::make('total')
                                    ->label('Subtotal')
                                    ->money('INR'),

                                TextEntry::make('discount_total')
                                    ->label('Discount total')
                                    ->money('INR'),

                                TextEntry::make('grand_total')
                                    ->label('Grand total')
                                    ->money('INR')
                                    ->weight('bold'),

                                TextEntry::make('primary_total')
                                    ->label('Primary total')
                                    ->money('INR')
                                    ->placeholder('—'),
                            ]),
                    ]),

                Section::make('Other details')
                    ->collapsible()
                    ->schema([
                        TextEntry::make('notes')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
