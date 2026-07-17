<?php

namespace App\Filament\Resources\LedgerAccounts\Schemas;

use App\Models\LedgerAccount;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LedgerAccountInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Ledger account')
                    ->schema([
                        Grid::make()
                            ->columns(4)
                            ->schema([
                                TextEntry::make('name')
                                    ->weight('medium'),

                                TextEntry::make('type')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => LedgerAccountForm::typeLabel($state)),

                                TextEntry::make('opening_balance')
                                    ->money('INR'),

                                TextEntry::make('balance')
                                    ->label('Current balance')
                                    ->state(function (LedgerAccount $record): string {
                                        $balance = $record->balanceForDisplay();

                                        return sprintf('₹%s %s', number_format($balance['amount'], 2), $balance['side']);
                                    })
                                    ->weight('bold'),
                            ]),
                    ]),

                Section::make('Entries')
                    ->schema([
                        RepeatableEntry::make('entries')
                            ->label('Ledger entries')
                            ->schema([
                                Grid::make()
                                    ->columns(4)
                                    ->schema([
                                        TextEntry::make('entry_date')
                                            ->label('Date')
                                            ->dateTime('d M Y H:i'),

                                        TextEntry::make('voucher_no')
                                            ->label('Voucher'),

                                        TextEntry::make('debit')
                                            ->money('INR'),

                                        TextEntry::make('credit')
                                            ->money('INR'),
                                    ]),
                            ])
                            ->contained()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
