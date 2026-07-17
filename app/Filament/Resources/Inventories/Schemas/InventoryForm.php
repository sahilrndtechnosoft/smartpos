<?php

namespace App\Filament\Resources\Inventories\Schemas;

use App\Filament\Forms\PosFormFields;
use App\Models\Inventory;
use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class InventoryForm
{
    public static function configure(Schema $schema, bool $includeSupplier = true): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Details')
                    ->schema([
                        Grid::make()
                            ->columns(3)
                            ->schema([
                                ...($includeSupplier ? [
                                    Select::make('supplier_id')
                                        ->label('Supplier')
                                        ->relationship('supplier', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->native(false),
                                ] : []),

                                DateTimePicker::make('date')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->seconds(true)
                                    ->displayFormat('M j, Y H:i:s'),

                                ToggleButtons::make('status')
                                    ->label('Payment Status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'completed' => 'Completed',
                                        'cancelled' => 'Cancelled',
                                    ])
                                    ->default('pending')
                                    ->required()
                                    ->inline()
                                    ->grouped(),
                            ]),

                        Toggle::make('tax_inclusive')
                            ->label('Prices include tax')
                            ->helperText('Off = tax is added on top of the cost price. On = the cost price already includes tax.')
                            ->default(false)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set): void {
                                foreach ($get('items') ?? [] as $index => $item) {
                                    $set(
                                        "items.{$index}.tax_total",
                                        self::computeTax(
                                            (float) ($item['qty'] ?? 0),
                                            (float) ($item['purchase_rate'] ?? 0),
                                            self::taxRateForProduct($item['product_id'] ?? null),
                                            (bool) $get('tax_inclusive'),
                                        ),
                                    );
                                }
                            }),

                        TextInput::make('code')
                            ->required()
                            ->maxLength(255)
                            ->unique(table: Inventory::class, ignoreRecord: true)
                            ->hidden()
                            ->dehydrated(),
                    ]),

                Section::make('Items Details')
                    ->schema([
                        PosFormFields::barcodeScan(withSecondaryPurchase: true),

                        Repeater::make('items')
                            ->relationship()
                            ->label('Items')
                            ->schema(self::itemFields())
                            ->addActionLabel('Add to items')
                            ->collapsible()
                            ->cloneable()
                            ->orderColumn('sr')
                            ->defaultItems(0)
                            ->mutateRelationshipDataBeforeCreateUsing(fn (array $data): array => self::normalizeItemData($data))
                            ->mutateRelationshipDataBeforeSaveUsing(fn (array $data): array => self::normalizeItemData($data))
                            ->columnSpanFull(),
                    ]),

                Section::make('Other Details')
                    ->collapsible()
                    ->schema([
                        FileUpload::make('file')
                            ->label('File')
                            ->directory('inventories')
                            ->columnSpanFull(),

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
                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                            if (blank($state)) {
                                return;
                            }

                            $product = Product::query()->find($state);

                            if (! $product) {
                                return;
                            }

                            $set('purchase_rate', $product->purchase_rate);
                            $set('rate_a', $product->rate_a);
                            $set('mrp', $product->mrp);
                            self::recalculateItemTax($set, $get);
                        }),

                    TextInput::make('qty')
                        ->label('Available Stock')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Set $set, Get $get) => self::recalculateItemTax($set, $get)),

                    TextInput::make('purchase_rate')
                        ->label('Cost price')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Set $set, Get $get) => self::recalculateItemTax($set, $get)),

                    TextInput::make('rate_a')
                        ->label('Sale Price')
                        ->numeric()
                        ->required()
                        ->minValue(0),

                    TextInput::make('mrp')
                        ->label('Old Price')
                        ->numeric()
                        ->required()
                        ->minValue(0),

                    TextInput::make('batch_no')
                        ->label('Batch no.'),

                    DatePicker::make('expiry_date')
                        ->label('Expiry date')
                        ->native(false),

                    Toggle::make('is_secondary')
                        ->label('Secondary')
                        ->helperText('Added via the secondary purchase window.')
                        ->disabled()
                        ->dehydrated(),
                ]),

            Grid::make()
                ->columns(2)
                ->schema([
                    TextInput::make('total_cost')
                        ->label('Total cost for this qty')
                        ->numeric()
                        ->minValue(0)
                        ->prefix('₹')
                        ->dehydrated(false)
                        ->live(onBlur: true)
                        ->helperText('Enter the supplier\'s total price for this line to back-calculate the per-piece cost.')
                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                            $qty = (float) ($get('qty') ?? 0);

                            if (blank($state) || $qty <= 0) {
                                return;
                            }

                            $set('purchase_rate', round((float) $state / $qty, 2));
                            self::recalculateItemTax($set, $get);
                        }),

                    TextInput::make('tax_total')
                        ->label('Tax amount')
                        ->numeric()
                        ->prefix('₹')
                        ->default(0)
                        ->disabled()
                        ->dehydrated(),
                ]),

            Grid::make()
                ->columns(2)
                ->visible(fn (Get $get): bool => self::secondaryUnitFor($get('product_id')) !== null)
                ->schema([
                    TextInput::make('box_qty')
                        ->label(fn (Get $get): string => 'Qty in '.(self::secondaryUnitFor($get('product_id'))['unit'] ?? 'boxes'))
                        ->numeric()
                        ->minValue(0)
                        ->dehydrated(false)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                            $secondaryUnit = self::secondaryUnitFor($get('product_id'));

                            if (! $secondaryUnit || blank($state)) {
                                return;
                            }

                            $set('qty', (int) round((float) $state * $secondaryUnit['qty']));
                        }),

                    TextInput::make('box_cost')
                        ->label(fn (Get $get): string => 'Total cost for that '.(self::secondaryUnitFor($get('product_id'))['unit'] ?? 'box'))
                        ->numeric()
                        ->minValue(0)
                        ->prefix('₹')
                        ->dehydrated(false)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                            $secondaryUnit = self::secondaryUnitFor($get('product_id'));

                            if (! $secondaryUnit || blank($state) || (float) $secondaryUnit['qty'] <= 0) {
                                return;
                            }

                            $set('purchase_rate', round((float) $state / $secondaryUnit['qty'], 2));
                            self::recalculateItemTax($set, $get);
                        }),
                ]),
        ];
    }

    protected static function recalculateItemTax(Set $set, Get $get): void
    {
        $inclusive = (bool) ($get('../../tax_inclusive') ?? false);

        $set('tax_total', self::computeTax(
            (float) ($get('qty') ?? 0),
            (float) ($get('purchase_rate') ?? 0),
            self::taxRateForProduct($get('product_id')),
            $inclusive,
        ));
    }

    protected static function taxRateForProduct(?string $productId): float
    {
        if (blank($productId)) {
            return 0.0;
        }

        $product = Product::query()->with('taxGroup.taxes')->find($productId);

        return $product?->taxGroup?->effectiveRate() ?? 0.0;
    }

    public static function computeTax(float $qty, float $rate, float $taxRatePercent, bool $inclusive): float
    {
        if ($qty <= 0 || $rate <= 0 || $taxRatePercent <= 0) {
            return 0.0;
        }

        $gross = round($qty * $rate, 2);

        if ($inclusive) {
            return round($gross - ($gross / (1 + ($taxRatePercent / 100))), 2);
        }

        return round($gross * $taxRatePercent / 100, 2);
    }

    /**
     * Authoritative tax recompute for every line, run at save time (the live in-form
     * recompute relies on a relative field lookup that may not always resolve).
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    public static function recalculateAllItemsTax(array $items, bool $inclusive): array
    {
        return collect($items)
            ->map(function ($item) use ($inclusive) {
                if (! is_array($item)) {
                    return $item;
                }

                $item['tax_total'] = self::computeTax(
                    (float) ($item['qty'] ?? 0),
                    (float) ($item['purchase_rate'] ?? 0),
                    self::taxRateForProduct($item['product_id'] ?? null),
                    $inclusive,
                );

                return $item;
            })
            ->all();
    }

    /**
     * @return array{unit: string, qty: float}|null
     */
    protected static function secondaryUnitFor(?string $productId): ?array
    {
        if (blank($productId)) {
            return null;
        }

        $product = Product::query()->find($productId);

        if (! $product || blank($product->secondary_unit) || blank($product->secondary_unit_qty) || (float) $product->secondary_unit_qty <= 0) {
            return null;
        }

        return ['unit' => $product->secondary_unit, 'qty' => (float) $product->secondary_unit_qty];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function normalizeItemData(array $data): array
    {
        $salePrice = $data['rate_a'] ?? 0;

        $data['rate_b'] = $data['rate_b'] ?? $salePrice;
        $data['rate_c'] = $data['rate_c'] ?? $salePrice;
        $data['is_locked'] = $data['is_locked'] ?? false;
        $data['is_secondary'] = $data['is_secondary'] ?? false;
        $data['tax_total'] = $data['tax_total'] ?? 0;

        return $data;
    }

    public static function buildLineItemFromProduct(Product $product, int $qty = 1): array
    {
        return self::normalizeItemData([
            'product_id' => $product->id,
            'qty' => $qty,
            'purchase_rate' => $product->purchase_rate,
            'rate_a' => $product->rate_a,
            'mrp' => $product->mrp,
        ]);
    }
}
