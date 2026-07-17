<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Filament\Forms\PosFormFields;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Support\ProductRateOptions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Str;

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
                                    ->default(fn (): string => Customer::cashCustomer()->id)
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
                                        'cod' => 'Cash',
                                        'online' => 'Online',
                                        'card' => 'Card',
                                        'upi' => 'UPI',
                                        'wallet' => 'Wallet',
                                        'credit' => 'Credit',
                                        'multi' => 'Multiple',
                                    ])
                                    ->default('cod')
                                    ->required()
                                    ->live()
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

                Section::make('Split payment')
                    ->description('Break this bill across multiple payment methods. Amounts must add up to the grand total.')
                    ->visible(fn (Get $get): bool => $get('payment_mode') === 'multi')
                    ->schema([
                        Repeater::make('splitPayments')
                            ->label('Payments')
                            ->schema([
                                Grid::make()
                                    ->columns(2)
                                    ->schema([
                                        Select::make('method')
                                            ->label('Method')
                                            ->options([
                                                'cod' => 'Cash',
                                                'online' => 'Online',
                                                'card' => 'Card',
                                                'upi' => 'UPI',
                                                'wallet' => 'Wallet',
                                            ])
                                            ->required()
                                            ->native(false),

                                        TextInput::make('amount')
                                            ->label('Amount')
                                            ->numeric()
                                            ->prefix('₹')
                                            ->minValue(0.01)
                                            ->required(),
                                    ]),
                            ])
                            ->addActionLabel('Add payment')
                            ->defaultItems(2)
                            ->dehydrated()
                            ->columnSpanFull(),
                    ]),

                Section::make('Line items')
                    ->schema([
                        PosFormFields::barcodeScan(),

                        Repeater::make('items')
                            ->relationship()
                            ->label('Items')
                            ->schema(self::itemFields())
                            ->addActionLabel('Add item')
                            ->collapsible()
                            ->cloneable()
                            ->defaultItems(0)
                            ->itemLabel(fn (array $state): ?string => filled($state['product_name'] ?? null)
                                ? sprintf('%s × %s', $state['product_name'], $state['qty'] ?? 1)
                                : null)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set): void {
                                self::syncOrderTotals($get, $set);
                            })
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
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('discount_total')
                                    ->label('Discount total')
                                    ->numeric()
                                    ->prefix('₹')
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('grand_total')
                                    ->label('Grand total')
                                    ->numeric()
                                    ->prefix('₹')
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('primary_total')
                                    ->label('Primary total')
                                    ->numeric()
                                    ->prefix('₹')
                                    ->default(0)
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
                        ->label('Qty')
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, Get $get): void {
                            $product = Product::query()->find($get('product_id'));
                            $rateKey = $get('product_snapshot.applied_rate') ?? 'rate_a';

                            if ($product) {
                                self::applyProductRate($set, $get, $product, $rateKey);

                                return;
                            }

                            self::recalculateItem($set, $get);
                        }),

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
        $qty = (int) ($get('qty') ?? 1);

        $set('product_snapshot.applied_rate', $rateKey);
        $set('unit_price', ProductRateOptions::priceFor($product, $rateKey, $qty));
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
                    $data['unit_price'] = ProductRateOptions::priceFor($product, $rateKey, $qty);
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

    public static function syncOrderTotals(Get $get, Set $set): void
    {
        $items = $get('items') ?? [];
        $withSchemes = self::applySchemes($items);

        if ($withSchemes !== $items) {
            $set('items', $withSchemes);
        }

        foreach (self::calculateOrderTotals($withSchemes) as $field => $value) {
            $set($field, $value);
        }
    }

    /**
     * Adds/updates auto-generated free lines for products with a "buy X get Y free"
     * scheme, based on the qty of their real (non-free) line. Existing free lines are
     * rebuilt from scratch each time so they always match the current source qtys.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    public static function applySchemes(array $items): array
    {
        $sourceLines = collect($items)
            ->filter(fn ($item): bool => is_array($item) && data_get($item, 'product_snapshot.is_scheme_free') !== true)
            ->values()
            ->all();

        $freeLines = [];

        foreach ($sourceLines as $item) {
            $productId = $item['product_id'] ?? null;
            $qty = (int) ($item['qty'] ?? 0);

            if (blank($productId) || $qty <= 0) {
                continue;
            }

            $product = Product::query()->find($productId);

            if (! $product || blank($product->scheme_buy_qty) || blank($product->scheme_free_qty)) {
                continue;
            }

            $freeQty = intdiv($qty, $product->scheme_buy_qty) * $product->scheme_free_qty;

            if ($freeQty <= 0) {
                continue;
            }

            $freeProduct = filled($product->scheme_free_product_id)
                ? Product::query()->find($product->scheme_free_product_id)
                : $product;

            if (! $freeProduct) {
                continue;
            }

            if (isset($freeLines[$freeProduct->id])) {
                $freeLines[$freeProduct->id]['qty'] += $freeQty;
            } else {
                $freeLines[$freeProduct->id] = [
                    'product_id' => $freeProduct->id,
                    'product_name' => "{$freeProduct->name} (Free — scheme)",
                    'product_snapshot' => [
                        'applied_rate' => 'rate_a',
                        'is_scheme_free' => true,
                    ],
                    'qty' => $freeQty,
                    'unit_price' => 0,
                    'discount_type' => null,
                    'discount_value' => null,
                    'tax_total' => 0,
                ];
            }
        }

        $normalizedFreeLines = collect($freeLines)
            ->map(fn (array $line): array => self::normalizeItemData($line))
            ->values()
            ->all();

        return [...$sourceLines, ...$normalizedFreeLines];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<string, float>
     */
    public static function calculateOrderTotals(array $items): array
    {
        $normalized = collect($items)
            ->filter(fn ($item): bool => is_array($item))
            ->map(fn (array $item): array => self::normalizeItemData($item));

        return [
            'total' => round((float) $normalized->sum('subtotal'), 2),
            'discount_total' => round((float) $normalized->sum('discount_amount'), 2),
            'grand_total' => round((float) $normalized->sum('final_price'), 2),
            'primary_total' => round((float) $normalized->sum(fn (array $item): float => (float) ($item['primary_total'] ?? 0)), 2),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function applyOrderTotals(array $data): array
    {
        foreach (self::calculateOrderTotals($data['items'] ?? []) as $field => $value) {
            $data[$field] = $value;
        }

        return $data;
    }

    public static function buildLineItemFromProduct(Product $product, int $qty = 1): array
    {
        $rateKey = 'rate_a';
        $item = [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_snapshot' => [
                'applied_rate' => $rateKey,
            ],
            'qty' => $qty,
            'unit_price' => ProductRateOptions::priceFor($product, $rateKey, $qty),
            'discount_type' => null,
            'discount_value' => null,
            'tax_total' => 0,
        ];

        return self::normalizeItemData($item);
    }

    /**
     * Re-derives the unit price from the item's current rate type and qty (picking up
     * qty-wise rate slabs), then recalculates the line. Used when a barcode rescan bumps
     * the qty of an existing line, since {@see self::normalizeItemData()} alone only
     * fills the price when it's blank.
     *
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    public static function reapplyRateForQty(Product $product, array $item): array
    {
        $rateKey = data_get($item, 'product_snapshot.applied_rate') ?? 'rate_a';
        $item['unit_price'] = ProductRateOptions::priceFor($product, $rateKey, (int) ($item['qty'] ?? 1));

        return self::normalizeItemData($item);
    }

    /**
     * Validates the payment mode/split amounts against the real order total (computed
     * from the live line items, since Filament's Repeater relationship data isn't part
     * of the form's dehydrated state) and returns the payment rows to persist. Must run
     * before the order is created/saved, so an invalid split never reaches the database.
     *
     * @param  list<array<string, mixed>>  $items
     * @param  list<array<string, mixed>>  $rawSplitPayments
     * @return list<array{method: string, amount: float}>
     */
    public static function resolvePayments(?string $paymentMode, array $items, array $rawSplitPayments): array
    {
        $grandTotal = round((float) (self::calculateOrderTotals($items)['grand_total'] ?? 0), 2);

        if ($paymentMode === 'credit') {
            return [];
        }

        if ($paymentMode !== 'multi') {
            return [[
                'method' => $paymentMode,
                'amount' => $grandTotal,
            ]];
        }

        $payments = collect($rawSplitPayments)
            ->filter(fn ($payment): bool => is_array($payment) && filled($payment['method'] ?? null) && (float) ($payment['amount'] ?? 0) > 0)
            ->map(fn (array $payment): array => [
                'method' => $payment['method'],
                'amount' => round((float) $payment['amount'], 2),
            ])
            ->values();

        $paid = round((float) $payments->sum('amount'), 2);

        if (abs($paid - $grandTotal) > 0.01) {
            Notification::make()
                ->title('Split payment total mismatch')
                ->body("Split payments (₹{$paid}) must add up to the grand total (₹{$grandTotal}).")
                ->danger()
                ->send();

            throw new Halt;
        }

        return $payments->all();
    }

    /**
     * Replaces the order's payment records with the given rows.
     *
     * @param  list<array{method: string, amount: float}>  $payments
     */
    public static function syncPayments(Order $order, array $payments): void
    {
        $order->payments()->delete();

        foreach ($payments as $payment) {
            $order->payments()->create([
                'code' => 'PMT-'.Str::upper(Str::random(10)),
                'type' => 'credit',
                'method' => $payment['method'],
                'amount' => $payment['amount'],
                'status' => 'completed',
                'payment_at' => now(),
            ]);
        }
    }

    /**
     * @return list<array{method: string, amount: float}>
     */
    public static function splitPaymentsFromOrder(Order $order): array
    {
        return $order->payments
            ->map(fn (Payment $payment): array => [
                'method' => $payment->method,
                'amount' => (float) $payment->amount,
            ])
            ->all();
    }
}
