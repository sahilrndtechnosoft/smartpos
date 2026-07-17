<?php

namespace App\Filament\Resources\LedgerAccounts\Schemas;

use App\Models\LedgerAccount;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LedgerAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Ledger account')
                    ->schema([
                        Grid::make()
                            ->columns(3)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),

                                Select::make('type')
                                    ->options(fn (?LedgerAccount $record): array => ($record && in_array($record->type, ['sundry_debtor', 'sundry_creditor'], true))
                                        ? ['sundry_debtor' => 'Sundry Debtor', 'sundry_creditor' => 'Sundry Creditor']
                                        : [
                                            'bank' => 'Bank Account',
                                            'cash' => 'Cash in Hand',
                                            'expense' => 'Expense',
                                            'income' => 'Income',
                                        ])
                                    ->disabled(fn (?LedgerAccount $record): bool => (bool) ($record && in_array($record->type, ['sundry_debtor', 'sundry_creditor'], true)))
                                    ->dehydrated(fn (?LedgerAccount $record): bool => ! ($record && in_array($record->type, ['sundry_debtor', 'sundry_creditor'], true)))
                                    ->helperText('Sundry Debtor/Creditor accounts are auto-created for customers/suppliers.')
                                    ->required()
                                    ->native(false),

                                TextInput::make('opening_balance')
                                    ->numeric()
                                    ->prefix('₹')
                                    ->default(0)
                                    ->required(),
                            ]),

                        Textarea::make('notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            'bank' => 'Bank Account',
            'cash' => 'Cash in Hand',
            'expense' => 'Expense',
            'income' => 'Income',
            'sundry_debtor' => 'Sundry Debtor',
            'sundry_creditor' => 'Sundry Creditor',
            default => ucfirst($type),
        };
    }
}
