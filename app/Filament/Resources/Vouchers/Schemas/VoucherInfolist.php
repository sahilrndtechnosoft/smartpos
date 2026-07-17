<?php

namespace App\Filament\Resources\Vouchers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VoucherInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Voucher details')
                    ->schema([
                        Grid::make()
                            ->columns(3)
                            ->schema([
                                TextEntry::make('code')
                                    ->label('Voucher number')
                                    ->weight('medium'),

                                TextEntry::make('voucher_date')
                                    ->label('Date')
                                    ->dateTime('M j, Y H:i:s'),

                                TextEntry::make('amount')
                                    ->money('INR')
                                    ->weight('bold'),

                                TextEntry::make('debitLedgerAccount.name')
                                    ->label('Debit account'),

                                TextEntry::make('creditLedgerAccount.name')
                                    ->label('Credit account'),

                                TextEntry::make('narration')
                                    ->placeholder('—'),
                            ]),
                    ]),
            ]);
    }
}
