<?php

namespace App\Filament\Pages;

use App\Models\KeyboardShortcut;
use App\Support\KeyboardShortcutActionTypes;
use App\Support\KeyboardShortcutCombination;
use App\Support\KeyboardShortcutTargets;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use UnitEnum;

class KeyboardShortcutsHelp extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static ?string $navigationLabel = 'Shortcut help';

    protected static ?string $title = 'Keyboard shortcuts';

    protected static ?string $slug = 'keyboard-shortcuts-help';

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 4;

    protected static bool $isDiscovered = false;

    protected string $view = 'filament.pages.keyboard-shortcuts-help';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                KeyboardShortcut::query()
                    ->where('is_active', true)
                    ->orderBy('sr')
                    ->orderBy('name'),
            )
            ->columns([
                TextColumn::make('name')
                    ->weight('medium'),

                TextColumn::make('combination')
                    ->label('Shortcut')
                    ->formatStateUsing(fn (?string $state): string => KeyboardShortcutCombination::display($state))
                    ->badge(),

                TextColumn::make('action_type')
                    ->label('Action')
                    ->formatStateUsing(fn (string $state): string => KeyboardShortcutActionTypes::labels()[$state] ?? $state),

                TextColumn::make('action_target')
                    ->label('Target')
                    ->formatStateUsing(function (?string $state, KeyboardShortcut $record): string {
                        if (blank($state)) {
                            return '—';
                        }

                        if ($record->action_type === KeyboardShortcutActionTypes::CUSTOM_URL) {
                            return $state;
                        }

                        return KeyboardShortcutTargets::resourceOptions()[$state] ?? class_basename($state);
                    }),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->paginated(false)
            ->searchable(false);
    }
}
