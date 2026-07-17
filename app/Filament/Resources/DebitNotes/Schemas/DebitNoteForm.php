<?php

namespace App\Filament\Resources\DebitNotes\Schemas;

use App\Models\DebitNote;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DebitNoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Debit note details')
                    ->schema([
                        Grid::make()
                            ->columns(3)
                            ->schema([
                                Select::make('supplier_id')
                                    ->label('Supplier')
                                    ->relationship('supplier', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->native(false),

                                DateTimePicker::make('note_date')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->seconds(true)
                                    ->displayFormat('M j, Y H:i:s'),

                                TextInput::make('amount')
                                    ->numeric()
                                    ->prefix('₹')
                                    ->minValue(0.01)
                                    ->required(),
                            ]),

                        TextInput::make('reason')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('code')
                            ->required()
                            ->maxLength(32)
                            ->unique(table: DebitNote::class, ignoreRecord: true)
                            ->hidden()
                            ->dehydrated(),
                    ]),
            ]);
    }
}
