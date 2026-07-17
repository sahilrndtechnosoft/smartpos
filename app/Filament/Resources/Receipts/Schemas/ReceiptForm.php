<?php

namespace App\Filament\Resources\Receipts\Schemas;

use App\Models\Customer;
use App\Models\Receipt;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReceiptForm
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
                                Select::make('customer_id')
                                    ->label('Received from')
                                    ->relationship('customer', 'name')
                                    ->getOptionLabelFromRecordUsing(fn (Customer $record): string => filled($record->name)
                                        ? "{$record->name} ({$record->phone})"
                                        : $record->phone)
                                    ->searchable(['name', 'phone', 'email'])
                                    ->preload()
                                    ->required()
                                    ->native(false),

                                Select::make('ledger_account_id')
                                    ->label('Deposited to')
                                    ->relationship('ledgerAccount', 'name', fn ($query) => $query->whereIn('type', ['cash', 'bank']))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->native(false),

                                DateTimePicker::make('receipt_date')
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
                            ->unique(table: Receipt::class, ignoreRecord: true)
                            ->hidden()
                            ->dehydrated(),
                    ]),
            ]);
    }
}
