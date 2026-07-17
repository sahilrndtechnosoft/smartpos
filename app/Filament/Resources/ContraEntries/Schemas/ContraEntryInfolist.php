<?php

namespace App\Filament\Resources\ContraEntries\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContraEntryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Contra entry details')
                    ->schema([
                        Grid::make()
                            ->columns(3)
                            ->schema([
                                TextEntry::make('code')
                                    ->label('Contra number')
                                    ->weight('medium'),

                                TextEntry::make('entry_date')
                                    ->label('Date')
                                    ->dateTime('M j, Y H:i:s'),

                                TextEntry::make('amount')
                                    ->money('INR')
                                    ->weight('bold'),

                                TextEntry::make('fromLedgerAccount.name')
                                    ->label('From account'),

                                TextEntry::make('toLedgerAccount.name')
                                    ->label('To account'),

                                TextEntry::make('narration')
                                    ->placeholder('—'),
                            ]),
                    ]),
            ]);
    }
}
