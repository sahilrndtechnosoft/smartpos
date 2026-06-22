<?php

namespace App\Filament\Resources\KeyboardShortcuts\Schemas;

use App\Models\KeyboardShortcut;
use App\Support\KeyboardShortcutActionTypes;
use App\Support\KeyboardShortcutCombination;
use App\Support\KeyboardShortcutTargets;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class KeyboardShortcutForm
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
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('combination')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(table: KeyboardShortcut::class, ignoreRecord: true)
                                    ->helperText('Examples: CTRL+SHIFT+I, ALT+S, F2, ESC')
                                    ->dehydrateStateUsing(fn (?string $state): string => KeyboardShortcutCombination::normalize($state))
                                    ->rules([
                                        fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail): void {
                                            if (! KeyboardShortcutCombination::isValid($value)) {
                                                $fail('Enter a valid shortcut such as CTRL+SHIFT+P or F2.');
                                            }
                                        },
                                    ]),

                                Select::make('action_type')
                                    ->label('Action')
                                    ->options(KeyboardShortcutActionTypes::labels())
                                    ->required()
                                    ->native(false)
                                    ->live(),

                                Select::make('action_target')
                                    ->label('Module')
                                    ->options(KeyboardShortcutTargets::resourceOptions())
                                    ->searchable()
                                    ->native(false)
                                    ->visible(fn (Get $get): bool => in_array(
                                        $get('action_type'),
                                        KeyboardShortcutActionTypes::requiresResourceTarget(),
                                        true,
                                    ))
                                    ->required(fn (Get $get): bool => in_array(
                                        $get('action_type'),
                                        KeyboardShortcutActionTypes::requiresResourceTarget(),
                                        true,
                                    )),

                                TextInput::make('action_record_id')
                                    ->label('Record ID')
                                    ->helperText('Required for view, edit, and delete actions. Use the record UUID from the module.')
                                    ->maxLength(255)
                                    ->visible(fn (Get $get): bool => in_array(
                                        $get('action_type'),
                                        KeyboardShortcutActionTypes::requiresRecordTarget(),
                                        true,
                                    ))
                                    ->required(fn (Get $get): bool => in_array(
                                        $get('action_type'),
                                        KeyboardShortcutActionTypes::requiresRecordTarget(),
                                        true,
                                    )),

                                TextInput::make('action_target')
                                    ->label('URL path')
                                    ->placeholder('/harsh/adminpov/orders')
                                    ->maxLength(255)
                                    ->visible(fn (Get $get): bool => in_array(
                                        $get('action_type'),
                                        KeyboardShortcutActionTypes::requiresUrlTarget(),
                                        true,
                                    ))
                                    ->required(fn (Get $get): bool => in_array(
                                        $get('action_type'),
                                        KeyboardShortcutActionTypes::requiresUrlTarget(),
                                        true,
                                    )),

                                TextInput::make('sr')
                                    ->label('Sort order')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),

                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),
                            ]),

                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
