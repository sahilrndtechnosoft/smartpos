<?php

namespace App\Filament\Resources\LedgerAccounts\Tables;

use App\Filament\Resources\LedgerAccounts\Schemas\LedgerAccountForm;
use App\Models\LedgerAccount;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class LedgerAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => LedgerAccountForm::typeLabel($state))
                    ->sortable(),

                TextColumn::make('balance')
                    ->label('Balance')
                    ->state(function (LedgerAccount $record): string {
                        $balance = $record->balanceForDisplay();

                        return sprintf('₹%s %s', number_format($balance['amount'], 2), $balance['side']);
                    })
                    ->alignEnd(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->date('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->striped()
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'bank' => 'Bank Account',
                        'cash' => 'Cash in Hand',
                        'expense' => 'Expense',
                        'income' => 'Income',
                        'sundry_debtor' => 'Sundry Debtor',
                        'sundry_creditor' => 'Sundry Creditor',
                    ]),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make()
                        ->hidden(fn (LedgerAccount $record): bool => $record->is_system),
                ])
                    ->tooltip('Actions'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No ledger accounts yet');
    }
}
