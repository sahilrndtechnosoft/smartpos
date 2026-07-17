<?php

namespace App\Filament\Resources\Damages\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DamageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Damage details')
                    ->schema([
                        Grid::make()
                            ->columns(3)
                            ->schema([
                                TextEntry::make('code')
                                    ->label('Damage number')
                                    ->weight('medium'),

                                TextEntry::make('product.name')
                                    ->label('Product'),

                                TextEntry::make('damaged_at')
                                    ->label('Date')
                                    ->dateTime('M j, Y H:i:s'),

                                TextEntry::make('qty')
                                    ->label('Qty'),

                                TextEntry::make('cost_value')
                                    ->label('Cost value')
                                    ->money('INR'),

                                TextEntry::make('reason')
                                    ->placeholder('—'),

                                TextEntry::make('notes')
                                    ->placeholder('—')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
