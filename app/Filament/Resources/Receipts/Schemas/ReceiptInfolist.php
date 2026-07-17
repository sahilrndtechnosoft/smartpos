<?php

namespace App\Filament\Resources\Receipts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReceiptInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Receipt details')
                    ->schema([
                        Grid::make()
                            ->columns(3)
                            ->schema([
                                TextEntry::make('code')
                                    ->label('Receipt number')
                                    ->weight('medium'),

                                TextEntry::make('receipt_date')
                                    ->label('Date')
                                    ->dateTime('M j, Y H:i:s'),

                                TextEntry::make('amount')
                                    ->money('INR')
                                    ->weight('bold'),

                                TextEntry::make('customer.name')
                                    ->label('Received from'),

                                TextEntry::make('ledgerAccount.name')
                                    ->label('Deposited to'),

                                TextEntry::make('narration')
                                    ->placeholder('—'),
                            ]),
                    ]),
            ]);
    }
}
