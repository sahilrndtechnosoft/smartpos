<?php

namespace App\Filament\Resources\SaleReturns\Schemas;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SaleReturnForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Return details')
                    ->schema([
                        Grid::make()
                            ->columns(3)
                            ->schema([
                                Select::make('order_id')
                                    ->label('Sales order')
                                    ->searchable()
                                    ->getSearchResultsUsing(fn (string $search): array => Order::query()
                                        ->where('code', 'like', "%{$search}%")
                                        ->orWhereHas('customer', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                                        ->limit(20)
                                        ->get()
                                        ->mapWithKeys(fn (Order $order): array => [$order->id => self::orderOptionLabel($order)])
                                        ->all())
                                    ->getOptionLabelUsing(fn ($value): ?string => filled($value) && ($order = Order::query()->find($value))
                                        ? self::orderOptionLabel($order)
                                        : null)
                                    ->required()
                                    ->live()
                                    ->native(false)
                                    ->afterStateUpdated(function (Set $set, ?string $state): void {
                                        $order = filled($state) ? Order::query()->find($state) : null;

                                        $set('customer_id', $order?->customer_id);
                                        $set('items', self::returnableItemsFor($state));
                                        $set('total', 0);
                                    }),

                                DateTimePicker::make('returned_at')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->seconds(true)
                                    ->displayFormat('M j, Y H:i:s'),

                                Select::make('refund_mode')
                                    ->label('Refund via')
                                    ->options([
                                        'cod' => 'Cash',
                                        'online' => 'Online',
                                        'card' => 'Card',
                                        'upi' => 'UPI',
                                        'wallet' => 'Wallet',
                                        'credit' => 'Store credit',
                                    ])
                                    ->default('cod')
                                    ->required()
                                    ->native(false),
                            ]),

                        Textarea::make('reason')
                            ->rows(2)
                            ->columnSpanFull(),

                        Hidden::make('customer_id')
                            ->required(),

                        Hidden::make('current_return_id')
                            ->dehydrated(false),

                        TextInput::make('code')
                            ->required()
                            ->maxLength(32)
                            ->unique(table: SaleReturn::class, ignoreRecord: true)
                            ->hidden()
                            ->dehydrated(),
                    ]),

                Section::make('Items to return')
                    ->schema([
                        Repeater::make('items')
                            ->label('Items')
                            ->schema(self::itemFields())
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->defaultItems(0)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set): void {
                                $set('total', round((float) collect($get('items') ?? [])->sum('amount'), 2));
                            })
                            ->itemLabel(fn (array $state): ?string => $state['product_name'] ?? null)
                            ->collapsible()
                            ->columnSpanFull(),
                    ]),

                Section::make('Totals')
                    ->schema([
                        TextInput::make('total')
                            ->label('Refund total')
                            ->numeric()
                            ->prefix('₹')
                            ->default(0)
                            ->disabled()
                            ->dehydrated(),
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
                ->columns(4)
                ->schema([
                    Hidden::make('order_item_id')
                        ->required(),

                    Hidden::make('product_id')
                        ->required(),

                    TextInput::make('product_name')
                        ->label('Product')
                        ->disabled()
                        ->dehydrated(),

                    TextInput::make('unit_price')
                        ->label('Unit price')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(),

                    TextInput::make('qty')
                        ->label('Return qty')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->maxValue(fn (Get $get): int => self::remainingQtyForOrderItem(
                            $get('order_item_id'),
                            $get('../../current_return_id'),
                        ))
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, Get $get): void {
                            $qty = (float) ($get('qty') ?? 0);
                            $unitPrice = (float) ($get('unit_price') ?? 0);
                            $set('amount', round($qty * $unitPrice, 2));
                        }),

                    TextInput::make('amount')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(),
                ]),
        ];
    }

    protected static function orderOptionLabel(Order $order): string
    {
        return "{$order->code} — ".($order->customer?->name ?: $order->customer?->phone)." (₹{$order->grand_total})";
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function returnableItemsFor(?string $orderId): array
    {
        if (blank($orderId)) {
            return [];
        }

        $order = Order::query()->with('items')->find($orderId);

        if (! $order) {
            return [];
        }

        return $order->items
            ->map(function (OrderItem $item): ?array {
                $remaining = self::remainingQtyForOrderItem($item->id);

                if ($remaining <= 0) {
                    return null;
                }

                return [
                    'order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'unit_price' => (float) $item->unit_price,
                    'qty' => 0,
                    'amount' => 0,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public static function remainingQtyForOrderItem(?string $orderItemId, ?string $excludingSaleReturnId = null): int
    {
        if (blank($orderItemId)) {
            return 0;
        }

        $orderItem = OrderItem::query()->find($orderItemId);

        if (! $orderItem) {
            return 0;
        }

        $returned = SaleReturnItem::query()
            ->where('order_item_id', $orderItemId)
            ->when(filled($excludingSaleReturnId), fn ($query) => $query->where('sale_return_id', '!=', $excludingSaleReturnId))
            ->sum('qty');

        return max(0, $orderItem->qty - (int) $returned);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function itemsFromSaleReturn(SaleReturn $saleReturn): array
    {
        return $saleReturn->items
            ->map(fn (SaleReturnItem $item): array => [
                'order_item_id' => $item->order_item_id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'unit_price' => (float) $item->unit_price,
                'qty' => $item->qty,
                'amount' => (float) $item->amount,
            ])
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public static function persistItems(SaleReturn $saleReturn, array $items): void
    {
        $saleReturn->items()->delete();

        foreach ($items as $item) {
            $qty = (int) ($item['qty'] ?? 0);

            if ($qty <= 0) {
                continue;
            }

            $unitPrice = (float) ($item['unit_price'] ?? 0);

            $saleReturn->items()->create([
                'order_item_id' => $item['order_item_id'],
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'] ?? '',
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'amount' => round($qty * $unitPrice, 2),
            ]);
        }
    }

    public static function recordRefundPayment(SaleReturn $saleReturn): void
    {
        $saleReturn->payments()->delete();

        if ((float) $saleReturn->total <= 0) {
            return;
        }

        $saleReturn->payments()->create([
            'code' => 'RFD-'.Str::upper(Str::random(10)),
            'type' => 'debit',
            'method' => $saleReturn->refund_mode,
            'amount' => $saleReturn->total,
            'status' => 'completed',
            'payment_at' => now(),
        ]);
    }
}
