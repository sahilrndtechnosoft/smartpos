<?php

namespace App\Filament\Resources\CreditNotes\Schemas;

use App\Models\CreditNote;
use App\Models\Customer;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CreditNoteForm
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
                                Select::make('customer_id')
                                    ->label('Customer')
                                    ->relationship('customer', 'name')
                                    ->getOptionLabelFromRecordUsing(fn (Customer $record): string => filled($record->name)
                                        ? "{$record->name} ({$record->phone})"
                                        : $record->phone)
                                    ->searchable(['name', 'phone', 'email'])
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
                            ->unique(table: CreditNote::class, ignoreRecord: true)
                            ->hidden()
                            ->dehydrated(),
                    ]),
            ]);
    }
}
