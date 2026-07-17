<?php

namespace App\Filament\Resources\CreditNotes\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CreditNoteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Credit note details')
                    ->schema([
                        Grid::make()
                            ->columns(3)
                            ->schema([
                                TextEntry::make('code')
                                    ->label('Credit note number')
                                    ->weight('medium'),

                                TextEntry::make('note_date')
                                    ->label('Date')
                                    ->dateTime('M j, Y H:i:s'),

                                TextEntry::make('amount')
                                    ->money('INR')
                                    ->weight('bold'),

                                TextEntry::make('customer.name')
                                    ->label('Customer'),

                                TextEntry::make('reason')
                                    ->placeholder('—')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
