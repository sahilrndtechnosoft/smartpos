<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use App\Models\TaxGroup;
use App\Support\ProductRateOptions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->columns(['default' => 1, 'lg' => 3])
                    ->schema([
                        Group::make()
                            ->schema(self::mainColumn())
                            ->columnSpan(['default' => 1, 'lg' => 2]),

                        Group::make()
                            ->schema(self::sidebarColumn())
                            ->columnSpan(['default' => 1, 'lg' => 1]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @return array<int, Section>
     */
    protected static function mainColumn(): array
    {
        return [
            Section::make('Product Details')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, ?string $state, Get $get): void {
                            if (filled($get('sku'))) {
                                return;
                            }

                            $set('sku', Str::upper(Str::slug($state ?? '', '-')));
                        }),

                    TextInput::make('sku')
                        ->label('SKU')
                        ->required()
                        ->maxLength(255)
                        ->alphaDash()
                        ->unique(table: Product::class, ignoreRecord: true)
                        ->disabled()
                        ->dehydrated(),

                    TextInput::make('barcode')
                        ->required()
                        ->maxLength(255)
                        ->unique(table: Product::class, ignoreRecord: true),

                    TextInput::make('hsn_code')
                        ->label('HSN code')
                        ->maxLength(255),

                    TagsInput::make('tags')
                        ->label('Search tags / alternative names')
                        ->helperText('Used to improve product searchability.')
                        ->separator(',')
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Pricing')
                ->schema([
                    TextInput::make('rate_a')
                        ->label('Selling price')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->prefix('₹'),

                    TextInput::make('mrp')
                        ->label('Compare at price')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->prefix('₹'),

                    TextInput::make('purchase_rate')
                        ->label('Cost price')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->prefix('₹')
                        ->helperText('Customers won\'t see this price.'),

                    TextInput::make('rate_b')
                        ->label('Rate B')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->prefix('₹'),

                    TextInput::make('rate_c')
                        ->label('Rate C')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->prefix('₹'),
                ])
                ->columns(3),

            Section::make('Inventory')
                ->collapsible()
                ->schema([
                    TextInput::make('qty')
                        ->label('Available stock')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->required()
                        ->helperText('The available stock based on unit type.'),

                    TextInput::make('security_stock')
                        ->label('Security stock')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->required()
                        ->helperText('The safety stock limit which alerts you when the product is running low.'),

                    DatePicker::make('expired_at')
                        ->label('Expiry date')
                        ->native(false),
                ])
                ->columns(2),

            Section::make('Quantity-wise rate slabs')
                ->description('Charge a different per-unit price once the billed quantity reaches a threshold, per rate type. e.g. Rate A at qty 24 = ₹9.17.')
                ->collapsible()
                ->schema([
                    Repeater::make('product_discounts')
                        ->hiddenLabel()
                        ->schema([
                            Select::make('rate_type')
                                ->label('Rate type')
                                ->options(ProductRateOptions::labels())
                                ->required()
                                ->native(false),

                            TextInput::make('qty')
                                ->label('Min qty')
                                ->numeric()
                                ->required()
                                ->minValue(1),

                            TextInput::make('price')
                                ->label('Price at this qty')
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->prefix('₹'),
                        ])
                        ->columns(3)
                        ->defaultItems(0)
                        ->addActionLabel('Add rate slab')
                        ->collapsible()
                        ->columnSpanFull(),
                ]),

            Section::make('Free scheme')
                ->description('e.g. buy 12, get 1 free. Leave the free item blank to give the same product free.')
                ->collapsible()
                ->schema([
                    TextInput::make('scheme_buy_qty')
                        ->label('Buy qty')
                        ->numeric()
                        ->minValue(1),

                    TextInput::make('scheme_free_qty')
                        ->label('Free qty')
                        ->numeric()
                        ->minValue(1),

                    Select::make('scheme_free_product_id')
                        ->label('Free item')
                        ->relationship('schemeFreeProduct', 'name')
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->placeholder('Same product'),
                ])
                ->columns(3),

            Section::make('Description & media')
                ->collapsible()
                ->schema([
                    RichEditor::make('description')
                        ->columnSpanFull(),

                    FileUpload::make('images')
                        ->label('Media')
                        ->image()
                        ->multiple()
                        ->reorderable()
                        ->directory('products')
                        ->panelLayout('grid')
                        ->columnSpanFull(),
                ]),
        ];
    }

    /**
     * @return array<int, Section>
     */
    protected static function sidebarColumn(): array
    {
        return [
            Section::make('Status')
                ->compact()
                ->schema([
                    Toggle::make('is_active')
                        ->label('Visible')
                        ->default(true)
                        ->helperText('When disabled, this product will be hidden from all sales channels.'),

                    Toggle::make('featured')
                        ->default(false),

                    DatePicker::make('published_at')
                        ->label('Publish date')
                        ->required()
                        ->default(now())
                        ->native(false),
                ]),

            Section::make('Associations')
                ->compact()
                ->schema([
                    Select::make('brand_id')
                        ->label('Company')
                        ->relationship('brand', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->native(false),

                    Select::make('categories')
                        ->relationship('categories', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->native(false),
                ]),

            Section::make('Product unit')
                ->compact()
                ->schema([
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
                        ->required()
                        ->native(false),

                    TextInput::make('unit_value')
                        ->label('Value')
                        ->numeric()
                        ->default(1)
                        ->minValue(0)
                        ->required()
                        ->placeholder('e.g., 1'),

                    TextInput::make('secondary_unit')
                        ->label('Secondary unit')
                        ->placeholder('e.g., BOX')
                        ->helperText('Optional. Lets purchase entry accept qty in this unit.'),

                    TextInput::make('secondary_unit_qty')
                        ->label('Base units per secondary unit')
                        ->numeric()
                        ->minValue(0)
                        ->placeholder('e.g., 12'),
                ]),

            Section::make('Shipping')
                ->compact()
                ->schema([
                    Toggle::make('backorder')
                        ->label('Back order')
                        ->default(false)
                        ->helperText('Allow orders when stock is unavailable.'),

                    Toggle::make('requires_shipping')
                        ->label('Requires shipping')
                        ->default(true),
                ]),

            Section::make('Tax category')
                ->compact()
                ->schema([
                    Select::make('tax_group_id')
                        ->label('Tax category')
                        ->options(fn (): array => TaxGroup::query()->orderBy('name')->pluck('name', 'id')->all())
                        ->searchable()
                        ->preload()
                        ->native(false),
                ]),
        ];
    }
}
