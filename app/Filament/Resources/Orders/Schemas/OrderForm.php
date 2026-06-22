<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Support\ProductRateOptions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Order details')
                    ->schema([
                        Grid::make()
                            ->columns(3)
                            ->schema([
                                Select::make('customer_id')
                                    ->label('Customer')
                                    ->relationship('customer', 'name')
                                    ->getOptionLabelFromRecordUsing(fn (Customer $record): string => filled($record->name)
                                        ? "{$record->name} ({$record->phone})"
                                        : $record->phone)
                                    ->searchable(['name', 'phone', 'email'])
                                    ->preload()
                                    ->required()
                                    ->native(false),

                                DateTimePicker::make('ordered_at')
                                    ->label('Ordered at')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->seconds(true)
                                    ->displayFormat('M j, Y H:i:s'),

                                ToggleButtons::make('payment_mode')
                                    ->label('Payment mode')
                                    ->options([
                                        'cod' => 'COD',
                                        'online' => 'Online',
                                        'card' => 'Card',
                                        'upi' => 'UPI',
                                        'wallet' => 'Wallet',
                                    ])
                                    ->default('cod')
                                    ->required()
                                    ->inline()
                                    ->grouped(),
                            ]),

                        TextInput::make('code')
                            ->required()
                            ->maxLength(32)
                            ->unique(table: Order::class, ignoreRecord: true)
                            ->hidden()
                            ->dehydrated(),
                    ]),

                Section::make('Line items')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->label('Items')
                            ->schema(self::itemFields())
                            ->addActionLabel('Add item')
                            ->collapsible()
                            ->cloneable()
                            ->defaultItems(0)
                            ->mutateRelationshipDataBeforeCreateUsing(fn (array $data): array => self::normalizeItemData($data))
                            ->mutateRelationshipDataBeforeSaveUsing(fn (array $data): array => self::normalizeItemData($data))
                            ->columnSpanFull(),
                    ]),

                Section::make('Totals')
                    ->schema([
                        Grid::make()
                            ->columns(4)
                            ->schema([
                                TextInput::make('total')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('₹')
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('discount_total')
                                    ->label('Discount total')
                                    ->numeric()
                                    ->prefix('₹')
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('grand_total')
                                    ->label('Grand total')
                                    ->numeric()
                                    ->prefix('₹')
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('primary_total')
                                    ->label('Primary total')
                                    ->numeric()
                                    ->prefix('₹')
                                    ->disabled()
                                    ->dehydrated(),
                            ]),
                    ]),

                Section::make('Other details')
                    ->collapsible()
                    ->schema([
                        Textarea::make('notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    public static function itemFields(): array
    {
        return [
            Grid::make()
                ->columns(6)
                ->schema([
                    Select::make('product_id')
                        ->label('Product')
                        ->relationship('product', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->native(false)
                        ->live()
                        ->columnSpan(2)
                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                            if (blank($state)) {
                                return;
                            }

                            $product = Product::query()->find($state);

                            if (! $product) {
                                return;
                            }

                            $set('product_name', $product->name);
                            self::applyProductRate($set, $get, $product, 'rate_a');
                        }),

                    Select::make('product_snapshot.applied_rate')
                        ->label('Rate')
                        ->options(fn (Get $get): array => ProductRateOptions::forProduct(
                            filled($get('product_id')) ? Product::query()->find($get('product_id')) : null,
                        ))
                        ->default('rate_a')
                        ->required()
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                            $product = Product::query()->find($get('product_id'));

                            if (! $product || blank($state)) {
                                return;
                            }

                            self::applyProductRate($set, $get, $product, $state);
                        }),

                    TextInput::make('qty')
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Set $set, Get $get) => self::recalculateItem($set, $get)),

                    TextInput::make('unit_price')
                        ->label('Unit price')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Set $set, Get $get) => self::recalculateItem($set, $get)),

                    TextInput::make('subtotal')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(),
                ]),

            Grid::make()
                ->columns(4)
                ->schema([
                    Select::make('discount_type')
                        ->label('Discount type')
                        ->options([
                            'fix' => 'Fixed',
                            'percent' => 'Percent',
                        ])
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(fn (Set $set, Get $get) => self::recalculateItem($set, $get)),

                    TextInput::make('discount_value')
                        ->label('Discount value')
                        ->numeric()
                        ->minValue(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Set $set, Get $get) => self::recalculateItem($set, $get)),

                    TextInput::make('discount_amount')
                        ->label('Discount amount')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(),

                    TextInput::make('tax_total')
                        ->label('Tax total')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Set $set, Get $get) => self::recalculateItem($set, $get)),
                ]),

            Grid::make()
                ->columns(2)
                ->schema([
                    TextInput::make('final_price')
                        ->label('Final price')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(),

                    TextInput::make('product_name')
                        ->hidden()
                        ->dehydrated(),
                ]),
        ];
    }

    public static function applyProductRate(Set $set, Get $get, Product $product, string $rateKey): void
    {
        $set('product_snapshot.applied_rate', $rateKey);
        $set('unit_price', ProductRateOptions::priceFor($product, $rateKey));
        self::recalculateItem($set, $get);
    }

    public static function recalculateItem(Set $set, Get $get): void
    {
        $qty = (float) ($get('qty') ?? 0);
        $unitPrice = (float) ($get('unit_price') ?? 0);
        $subtotal = round($qty * $unitPrice, 2);

        $set('subtotal', $subtotal);

        $discountType = $get('discount_type');
        $discountValue = (float) ($get('discount_value') ?? 0);
        $discountAmount = 0.0;

        if ($discountType === 'percent') {
            $discountAmount = round($subtotal * $discountValue / 100, 2);
        } elseif ($discountType === 'fix') {
            $discountAmount = min($discountValue, $subtotal);
        }

        $set('discount_amount', $discountAmount);

        $taxTotal = (float) ($get('tax_total') ?? 0);
        $set('final_price', round($subtotal - $discountAmount + $taxTotal, 2));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function normalizeItemData(array $data): array
    {
        $qty = (int) ($data['qty'] ?? 0);
        $unitPrice = (float) ($data['unit_price'] ?? 0);
        $subtotal = round($qty * $unitPrice, 2);

        $discountType = $data['discount_type'] ?? null;
        $discountValue = (float) ($data['discount_value'] ?? 0);
        $discountAmount = 0.0;

        if ($discountType === 'percent') {
            $discountAmount = round($subtotal * $discountValue / 100, 2);
        } elseif ($discountType === 'fix') {
            $discountAmount = min($discountValue, $subtotal);
        }

        $taxTotal = (float) ($data['tax_total'] ?? 0);
        $finalPrice = round($subtotal - $discountAmount + $taxTotal, 2);

        $data['subtotal'] = $subtotal;
        $data['discount_amount'] = $discountAmount;
        $data['final_price'] = $finalPrice;
        $data['tax_total'] = $taxTotal;

        $productId = $data['product_id'] ?? null;

        if ($productId) {
            $product = Product::query()->find($productId);

            if ($product) {
                $rateKey = data_get($data, 'product_snapshot.applied_rate')
                    ?? ProductRateOptions::detectFromSnapshot(
                        is_array($data['product_snapshot'] ?? null) ? $data['product_snapshot'] : null,
                        $unitPrice,
                    );

                $data['product_name'] = $data['product_name'] ?? $product->name;
                $data['product_snapshot'] = array_merge(
                    is_array($data['product_snapshot'] ?? null) ? $data['product_snapshot'] : [],
                    [
                        'sku' => $product->sku,
                        'mrp' => $product->mrp,
                        'rate_a' => $product->rate_a,
                        'rate_b' => $product->rate_b,
                        'rate_c' => $product->rate_c,
                        'applied_rate' => $rateKey,
                        'is_secondary' => $product->is_secondary,
                    ],
                );

                if (blank($data['unit_price'] ?? null)) {
                    $data['unit_price'] = ProductRateOptions::priceFor($product, $rateKey);
                }

                if ($product->is_secondary) {
                    $data['secondary_total'] = $finalPrice;
                    $data['primary_total'] = null;
                } else {
                    $data['primary_total'] = $finalPrice;
                    $data['secondary_total'] = null;
                }
            }
        }

        return $data;
    }
}
