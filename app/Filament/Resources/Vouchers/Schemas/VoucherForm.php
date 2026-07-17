<?php

namespace App\Filament\Resources\Vouchers\Schemas;

use App\Models\Voucher;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VoucherForm
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
                                DateTimePicker::make('voucher_date')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->seconds(true)
                                    ->displayFormat('M j, Y H:i:s'),

                                Select::make('debit_ledger_account_id')
                                    ->label('Debit account')
                                    ->relationship('debitLedgerAccount', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->native(false),

                                Select::make('credit_ledger_account_id')
                                    ->label('Credit account')
                                    ->relationship('creditLedgerAccount', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->native(false)
                                    ->different('debit_ledger_account_id'),
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
                            ->unique(table: Voucher::class, ignoreRecord: true)
                            ->hidden()
                            ->dehydrated(),
                    ]),
            ]);
    }
}
