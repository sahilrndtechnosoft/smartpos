<?php

namespace App\Filament\Resources\ContraEntries\Schemas;

use App\Models\ContraEntry;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContraEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Contra entry details')
                    ->description('Transfer money between your cash and bank accounts.')
                    ->schema([
                        Grid::make()
                            ->columns(3)
                            ->schema([
                                Select::make('from_ledger_account_id')
                                    ->label('From account')
                                    ->relationship('fromLedgerAccount', 'name', fn ($query) => $query->whereIn('type', ['cash', 'bank']))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->native(false),

                                Select::make('to_ledger_account_id')
                                    ->label('To account')
                                    ->relationship('toLedgerAccount', 'name', fn ($query) => $query->whereIn('type', ['cash', 'bank']))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->native(false)
                                    ->different('from_ledger_account_id'),

                                DateTimePicker::make('entry_date')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->seconds(true)
                                    ->displayFormat('M j, Y H:i:s'),
                            ]),

                        Grid::make()
                            ->columns(2)
                            ->schema([
                                TextInput::make('amount')
                                    ->numeric()
                                    ->prefix('₹')
                                    ->minValue(0.01)
                                    ->required(),

                                TextInput::make('narration')
                                    ->maxLength(255),
                            ]),

                        TextInput::make('code')
                            ->required()
                            ->maxLength(32)
                            ->unique(table: ContraEntry::class, ignoreRecord: true)
                            ->hidden()
                            ->dehydrated(),
                    ]),
            ]);
    }
}
