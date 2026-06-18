<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product information')
                    ->icon(Heroicon::OutlinedCube)
                    ->description('Basic details used across sales, inventory, and reporting.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                $set('slug', Str::slug($state ?? ''));
                            })
                            ->columnSpanFull(),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->alphaDash()
                            ->unique(table: Product::class, ignoreRecord: true)
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('barcode')
                            ->maxLength(255),

                        TextInput::make('category')
                            ->maxLength(255),

                        TextInput::make('sub_category')
                            ->maxLength(255),

                        Select::make('unit')
                            ->options([
                                'PCS' => 'PCS',
                                'KG' => 'KG',
                                'GM' => 'GM',
                                'LTR' => 'LTR',
                                'ML' => 'ML',
                                'BOX' => 'BOX',
                                'PKT' => 'PKT',
                            ])
                            ->default('PCS')
                            ->required()
                            ->native(false),

                        TextInput::make('pieces_per_box')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required(),

                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Tax, stock & status')
                    ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                    ->description('Pricing, reorder rules, and product availability.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('hsn_code')
                            ->maxLength(255),

                        TextInput::make('tax_rate')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->required(),

                        TextInput::make('reorder_level')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->required(),

                        TextInput::make('reorder_qty')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->required(),

                        Toggle::make('track_expiry')
                            ->default(false),

                        Toggle::make('is_active')
                            ->default(true),

                        Toggle::make('is_secondary')
                            ->default(false),
                    ]),
            ]);
    }
}
