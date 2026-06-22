<?php

namespace App\Filament\Resources\KeyboardShortcuts\Tables;

use App\Support\KeyboardShortcutActionTypes;
use App\Support\KeyboardShortcutCombination;
use App\Support\KeyboardShortcutTargets;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class KeyboardShortcutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('combination')
                    ->label('Shortcut')
                    ->formatStateUsing(fn (?string $state): string => KeyboardShortcutCombination::display($state))
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('action_type')
                    ->label('Action')
                    ->formatStateUsing(fn (string $state): string => KeyboardShortcutActionTypes::labels()[$state] ?? $state)
                    ->sortable(),

                TextColumn::make('action_target')
                    ->label('Target')
                    ->formatStateUsing(function (?string $state, $record): string {
                        if (blank($state)) {
                            return '—';
                        }

                        if ($record->action_type === KeyboardShortcutActionTypes::CUSTOM_URL) {
                            return $state;
                        }

                        $label = KeyboardShortcutTargets::resourceOptions()[$state] ?? class_basename($state);

                        if (filled($record->action_record_id)) {
                            return "{$label} · {$record->action_record_id}";
                        }

                        return $label;
                    })
                    ->wrap(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('sr')
                    ->label('Order')
                    ->alignEnd()
                    ->sortable(),
            ])
            ->defaultSort('sr')
            ->striped()
            ->filters([
                SelectFilter::make('action_type')
                    ->label('Action')
                    ->options(KeyboardShortcutActionTypes::labels()),

                TernaryFilter::make('is_active')
                    ->label('Active'),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->tooltip('Actions'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No keyboard shortcuts yet')
            ->emptyStateDescription('Create shortcuts to navigate modules quickly from anywhere in the panel.')
            ->emptyStateIcon(Heroicon::OutlinedCommandLine);
    }
}
