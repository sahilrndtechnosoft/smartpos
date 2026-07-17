<?php

namespace App\Filament\Resources\Damages\Schemas;

use App\Models\Damage;
use App\Models\Product;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class DamageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Damage details')
                    ->schema([
                        Grid::make()
                            ->columns(3)
                            ->schema([
                                Select::make('product_id')
                                    ->label('Product')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->native(false)
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                                        $product = filled($state) ? Product::query()->find($state) : null;
                                        $set('cost_value', round((float) ($get('qty') ?? 0) * (float) ($product->purchase_rate ?? 0), 2));
                                    }),

                                TextInput::make('qty')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, Get $get): void {
                                        $product = filled($get('product_id')) ? Product::query()->find($get('product_id')) : null;
                                        $set('cost_value', round((float) ($get('qty') ?? 0) * (float) ($product->purchase_rate ?? 0), 2));
                                    }),

                                DateTimePicker::make('damaged_at')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->seconds(true)
                                    ->displayFormat('M j, Y H:i:s'),
                            ]),

                        Grid::make()
                            ->columns(2)
                            ->schema([
                                TextInput::make('reason')
                                    ->maxLength(255)
                                    ->placeholder('e.g. Breakage, expired, spillage'),

                                TextInput::make('cost_value')
                                    ->label('Cost value written off')
                                    ->numeric()
                                    ->prefix('₹')
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(),
                            ]),

                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),

                        TextInput::make('code')
                            ->required()
                            ->maxLength(32)
                            ->unique(table: Damage::class, ignoreRecord: true)
                            ->hidden()
                            ->dehydrated(),
                    ]),
            ]);
    }
}
