<?php

namespace App\Filament\Resources\KeyboardShortcuts\Schemas;

use App\Support\KeyboardShortcutActionTypes;
use App\Support\KeyboardShortcutCombination;
use App\Support\KeyboardShortcutTargets;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class KeyboardShortcutInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->schema([
                        Grid::make()
                            ->columns(2)
                            ->schema([
                                TextEntry::make('name'),

                                TextEntry::make('combination')
                                    ->label('Shortcut')
                                    ->formatStateUsing(fn (?string $state): string => KeyboardShortcutCombination::display($state)),

                                TextEntry::make('action_type')
                                    ->label('Action')
                                    ->formatStateUsing(fn (string $state): string => KeyboardShortcutActionTypes::labels()[$state] ?? $state),

                                TextEntry::make('action_target')
                                    ->label('Module')
                                    ->formatStateUsing(function (?string $state, $record): string {
                                        if (blank($state)) {
                                            return '—';
                                        }

                                        if ($record->action_type === KeyboardShortcutActionTypes::CUSTOM_URL) {
                                            return $state;
                                        }

                                        return KeyboardShortcutTargets::resourceOptions()[$state] ?? class_basename($state);
                                    }),

                                TextEntry::make('action_record_id')
                                    ->label('Record ID')
                                    ->placeholder('—'),

                                TextEntry::make('sr')
                                    ->label('Sort order'),

                                IconEntry::make('is_active')
                                    ->label('Active')
                                    ->boolean(),
                            ]),

                        TextEntry::make('description')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
